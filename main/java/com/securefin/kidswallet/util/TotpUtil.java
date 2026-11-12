package com.securefin.kidswallet.util;

import org.apache.commons.codec.binary.Base32;
import org.springframework.stereotype.Component;

import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;
import java.security.SecureRandom;

@Component
public class TotpUtil {

    public static String generateSecretKey() {
        SecureRandom random = new SecureRandom();
        byte[] bytes = new byte[20];
        random.nextBytes(bytes);
        Base32 base32 = new Base32();
        return base32.encodeToString(bytes);
    }

    public static String getQrCodeUrl(String email, String secret) {
        return email;
    }

    public static boolean verifyCode(String totpSecret, String totpCode) {
        return false;
    }

    public boolean verifyTotp(String secretKey, String codeStr) {
        if (codeStr == null || !codeStr.matches("\\d{6}")) return false;
        long code = Long.parseLong(codeStr);
        long currentInterval = System.currentTimeMillis() / 1000 / 30;

        // Check current interval +- 1 interval to account for slight clock drift
        for (int i = -1; i <= 1; i++) {
            long hash = generateCode(secretKey, currentInterval + i);
            if (hash == code) return true;
        }
        return false;
    }

    private long generateCode(String secretKey, long interval) {
        try {
            Base32 base32 = new Base32();
            byte[] bytes = base32.decode(secretKey);
            byte[] data = new byte[8];
            for (int i = 8; i-- > 0; interval >>>= 8) {
                data[i] = (byte) interval;
            }

            SecretKeySpec signKey = new SecretKeySpec(bytes, "HmacSHA1");
            Mac mac = Mac.getInstance("HmacSHA1");
            mac.init(signKey);
            byte[] hash = mac.doFinal(data);

            int offset = hash[20 - 1] & 0xF;
            long truncatedHash = 0;
            for (int i = 0; i < 4; ++i) {
                truncatedHash <<= 8;
                truncatedHash |= (hash[offset + i] & 0xFF);
            }
            truncatedHash &= 0x7FFFFFFF;
            truncatedHash %= 1000000;
            return truncatedHash;
        } catch (Exception e) {
            return -1;
        }
    }
}
