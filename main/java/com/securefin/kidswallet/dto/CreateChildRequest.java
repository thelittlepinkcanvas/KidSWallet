package com.securefin.kidswallet.dto;

import jakarta.validation.constraints.*;

public class CreateChildRequest {

    @NotBlank(message = "Child name is required.")
    @Pattern(regexp = "^[a-zA-Z\\s]+$", message = "Name cannot contain numbers or symbols.")
    private String name;

    @NotBlank(message = "Email is required.")
    @Email(message = "Invalid email format.")
    private String email;

    @NotBlank(message = "PIN is required.")
    @Pattern(regexp = "^\\d{4,6}$", message = "PIN must be between 4 and 6 digits.")
    private String pin;

    // Getters and Setters
    public String getName() { return name; }
    public void setName(String name) { this.name = name; }
    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }
    public String getPin() { return pin; }
    public void setPin(String pin) { this.pin = pin; }
}
