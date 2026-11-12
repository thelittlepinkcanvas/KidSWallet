package com.securefin.kidswallet.controller;

import com.securefin.kidswallet.dto.CreateChildRequest;
import com.securefin.kidswallet.service.AuthService;
import jakarta.validation.Valid;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.List;
import java.util.Map;

@RestController
@RequestMapping("/api/parent")
public class ParentController {

    private AuthService authService = null;

    public ParentController(AuthService authService) {
        this.authService = authService;
    }

    public ResponseEntity> createChild(@Valid @RequestBody CreateChildRequest request,
                                       @RequestParam String parentEmail) {
        authService.createChild(request, parentEmail);
        return ResponseEntity.ok(Map.of("message", "Child account created successfully."));
    }

    public ResponseEntity<List>> getPendingApprovals(@RequestParam String parentEmail) {
        return ResponseEntity.ok(authService.getPendingApprovals(parentEmail));
    }

    public ResponseEntity> respondToChildLogin(@RequestParam Long requestId,
                                               @RequestParam boolean approve,
                                               @RequestParam String parentEmail) {
        authService.respondToChildLogin(requestId, approve, parentEmail);
        String action = approve ? "approved" : "rejected";
        return ResponseEntity.ok(Map.of("message", "Login request " + action + " successfully."));
    }
}