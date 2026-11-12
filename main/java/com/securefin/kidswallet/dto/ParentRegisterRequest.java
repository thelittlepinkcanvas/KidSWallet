package com.securefin.kidswallet.dto;

import jakarta.validation.constraints.*;

public class ParentRegisterRequest {

    @NotBlank(message = "Name is required.")
    @Pattern(regexp = "^[a-zA-Z\\s]+$", message = "Name cannot contain numbers or symbols.")
    private String name;

    @NotBlank(message = "Email is required.")
    @Email(message = "Invalid email format.")
    private String email;

    @NotBlank(message = "Phone number is required.")
    @Pattern(regexp = "^\\+?[0-9]{10,15}$", message = "Invalid phone number format.")
    private String phoneNumber;

    @NotBlank(message = "Password is required.")
    @Size(min = 12, message = "Password must be at least 12 characters long.")
    @Pattern(
            regexp = "^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%^&+=!]).*$",
            message = "Password must contain uppercase, lowercase, digit, and special character."
    )
    private String password;

    // Getters and Setters
    public String getName() { return name; }
    public void setName(String name) { this.name = name; }

    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }

    public String getPhoneNumber() { return phoneNumber; }
    public void setPhoneNumber(String phoneNumber) { this.phoneNumber = phoneNumber; }

    public String getPassword() { return password; }
    public void setPassword(String password) { this.password = password; }

    public String getConfirmPassword() {
        return "";
    }
}