package com.securefin.kidswallet.service;

import com.securefin.kidswallet.dto.*;
import com.securefin.kidswallet.entity.*;
import com.securefin.kidswallet.repository.*;
import com.securefin.kidswallet.util.TotpUtil;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;

import java.time.LocalDateTime;
import java.util.*;

@Service
public class AuthService {

    private final UserRepository userRepository;
    private final LoginApprovalRepository approvalRepository;
    private final PasswordEncoder passwordEncoder;

    public AuthService(UserRepository userRepository,
                       LoginApprovalRepository approvalRepository,
                       PasswordEncoder passwordEncoder) {
        this.userRepository = userRepository;
        this.approvalRepository = approvalRepository;
        this.passwordEncoder = passwordEncoder;
    }

    // 1. Parent Registration
    public Map<String, String> registerParent(ParentRegisterRequest request) {
        if (userRepository.findByEmail(request.getEmail()).isPresent()) {
            throw new IllegalArgumentException("Email already registered.");
        }

        User parent = new User();
        parent.setName(request.getName());
        parent.setEmail(request.getEmail());
        parent.setPhoneNumber(request.getPhoneNumber());
        parent.setPasswordHash(passwordEncoder.encode(request.getPassword()));
        parent.setRole("ROLE_PARENT");

        String secret = TotpUtil.generateSecretKey();
        parent.setTotpSecret(secret);

        userRepository.save(parent);

        Map<String, String> response = new HashMap<>();
        response.put("message", "Parent account created successfully!");
        response.put("totpSecret", secret);
        response.put("totpUri", TotpUtil.getQrCodeUrl(request.getEmail(), secret));
        return response;
    }

    // 2. Step 1 Login: Check Email & Return Name
    public Map<String, Object> checkEmail(String email) {
        User user = userRepository.findByEmail(email)
                .orElseThrow(() -> new IllegalArgumentException("Invalid credentials"));

        Map<String, Object> res = new HashMap<>();
        res.put("name", user.getName());
        res.put("role", user.getRole());
        res.put("canContinue", true);
        return res;
    }

    // 3. Parent Login (Password + TOTP)
    public Map<String, String> loginParent(String email, String password, String totpCode) {
        User parent = userRepository.findByEmail(email)
                .orElseThrow(() -> new IllegalArgumentException("Invalid credentials"));

        if (!"ROLE_PARENT".equals(parent.getRole())) throw new IllegalArgumentException("Invalid credentials");
        checkLockout(parent);

        if (!passwordEncoder.matches(password, parent.getPasswordHash()) || !TotpUtil.verifyCode(parent.getTotpSecret(), totpCode)) {
            handleFailedAttempt(parent);
            throw new IllegalArgumentException("Invalid email, password, or MFA code.");
        }

        resetFailedAttempts(parent);

        Map<String, String> response = new HashMap<>();
        response.put("message", "Parent login successful.");
        response.put("role", "ROLE_PARENT");
        response.put("userEmail", parent.getEmail());
        return response;
    }

    // 4. Parent creates Child account
    public void createChild(CreateChildRequest request, String parentEmail) {
        User parent = userRepository.findByEmail(parentEmail)
                .orElseThrow(() -> new IllegalArgumentException("Parent not found"));

        if (userRepository.findByEmail(request.getEmail()).isPresent()) {
            throw new IllegalArgumentException("Email already in use.");
        }

        User child = new User();
        child.setName(request.getName());
        child.setEmail(request.getEmail());
        child.setPinHash(passwordEncoder.encode(request.getPin()));
        child.setRole("ROLE_CHILD");
        child.setParent(parent);

        userRepository.save(child);
    }

    // 5. Child Login Step 2: Validate PIN & Trigger Parent Approval Push
    public Map<String, String> initiateChildLogin(String email, String pin) {
        User child = userRepository.findByEmail(email)
                .orElseThrow(() -> new IllegalArgumentException("Invalid credentials"));

        if (!"ROLE_CHILD".equals(child.getRole())) throw new IllegalArgumentException("Invalid credentials");
        checkLockout(child);

        if (!passwordEncoder.matches(pin, child.getPinHash())) {
            handleFailedAttempt(child);
            throw new IllegalArgumentException("Invalid PIN.");
        }

        resetFailedAttempts(child);

        String approvalToken = UUID.randomUUID().toString();

        LoginApproval approval = new LoginApproval();
        approval.setChild(child);
        approval.setParent(child.getParent());
        approval.setStatus("PENDING");
        approval.setApprovalToken(approvalToken);
        approval.setCreatedAt(LocalDateTime.now());

        S save = approvalRepository.save(approval);

        Map<String, String> res = new HashMap<>();
        res.put("approvalToken", approvalToken);
        res.put("requestId", String.valueOf(approval.getId()));
        res.put("status", "PENDING");
        res.put("message", "PIN verified. Waiting for parent approval on their dashboard.");
        return res;
    }

    // 6. Child Polls Approval Status
    public Map<String, String> checkChildApprovalStatus(String approvalToken) {
        LoginApproval approval = approvalRepository.findByApprovalToken(approvalToken)
                .orElseThrow();

        Map<String, String> res = new HashMap<>();
        res.put("status", approval.getStatus());
        if ("APPROVED".equals(approval.getStatus())) {
            res.put("message", "Parent approved login. Redirecting...");
            res.put("email", approval.getChild().getEmail());
        }
        return res;
    }

    // 7. Parent Dashboard: Get Pending Child Approvals
    public List> getPendingApprovals(String parentEmail) {
        User parent = userRepository.findByEmail(parentEmail)
                .orElseThrow(() -> new IllegalArgumentException("Parent not found"));

        List<LoginApproval> pending = approvalRepository.findByParentIdAndStatus(parent.getId(), "PENDING");
        List> list = new ArrayList<>();
        for (LoginApproval app : pending) {
            Map<String, String> m = new HashMap<>();
            m.put("requestId", String.valueOf(app.getId()));
            m.put("approvalToken", app.getApprovalToken());
            m.put("childName", app.getChild().getName());
            m.put("time", app.getCreatedAt().toString());
            list.add(m);
        }
        return list;
    }

    // 8. Parent Action: Approve/Reject Login
    public void respondToChildLogin(Long requestId, boolean approve, String parentEmail) {
        User parent = userRepository.findByEmail(parentEmail)
                .orElseThrow(() -> new IllegalArgumentException("Parent not found"));

        LoginApproval approval = approvalRepository.findById(requestId)
                .orElseThrow(() -> new IllegalArgumentException("Request not found"));

        if (!approval.getParent().getId().equals(parent.getId())) {
            throw new SecurityException("Unauthorized cross-family action.");
        }

        approval.setStatus(approve ? "APPROVED" : "REJECTED");
        approvalRepository.save(approval);
    }

    // Lockout & Rate Limiting Mechanics (ASVS 2.2.1)
    private void checkLockout(User user) {
        if (user.isAccountLocked()) {
            throw new IllegalStateException("Account locked due to 5 failed login attempts. Contact support.");
        }
    }

    private void handleFailedAttempt(User user) {
        int attempts = user.getFailedAttempts() + 1;
        user.setFailedAttempts(attempts);
        if (attempts >= 5) {
            user.setAccountLocked(true);
        }
        userRepository.save(user);
    }

    private void resetFailedAttempts(User user) {
        if (user.getFailedAttempts() > 0) {
            user.setFailedAttempts(0);
            userRepository.save(user);
        }
    }
}
}