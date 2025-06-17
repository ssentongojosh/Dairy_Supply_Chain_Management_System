package com.dscms.java_server.Requests;

import jakarta.validation.constraints.NotBlank;
import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;
import org.springframework.stereotype.Component;
import org.springframework.web.multipart.MultipartFile;

@Component
@Data
@AllArgsConstructor
@NoArgsConstructor
public class ValidationRequest {

    @NotBlank(message = "Please upload a National ID")
    private MultipartFile nationalId;
    //private MultipartFile bankStatement;
    @NotBlank(message = "Please upload a URSB certificate")
    private MultipartFile ursbCertificate;
}
