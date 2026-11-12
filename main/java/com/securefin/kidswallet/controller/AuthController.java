package com.securefin.kidswallet.controller;

import com.securefin.kidswallet.dto.ParentRegisterRequest;
import com.securefin.kidswallet.service.AuthService;
import jakarta.validation.Valid;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.Map;

@RestController
@RequestMapping("/api/auth")
public class AuthController {

    private final AuthService authService;

    public AuthController(AuthService authService) {
        this.authService = authService;
    }

    @PostMapping("/register/parent")
    public ResponseEntity> registerParent(@Valid @RequestBody ParentRegisterRequest request) {
        return ResponseEntity.ok(authService.registerParent(request));
    }

    @PostMapping("/check-email")
    public ResponseEntity> checkEmail(@RequestParam String email) {
        return ResponseEntity.ok(authService.checkEmail(email));
    }

    @PostMapping("/login/parent")
    public ResponseEntity> loginParent(@RequestParam String email,
                                       @RequestParam String password,
                                       @RequestParam String totpCode) {
        return ResponseEntity.ok(authService.loginParent(email, password, totpCode));
    }

    @PostMapping("/login/child/initiate")
    public ResponseEntity> initiateChildLogin(@RequestParam String email,
                                              @RequestParam String pin) {
        return ResponseEntity.ok(authService.initiateChildLogin(email, pin));
    }

    @GetMapping("/login/child/status")
    public ResponseEntity> checkChildApprovalStatus(@RequestParam String approvalToken) {
        return ResponseEntity.ok(authService.checkChildApprovalStatus(approvalToken));
    }
}