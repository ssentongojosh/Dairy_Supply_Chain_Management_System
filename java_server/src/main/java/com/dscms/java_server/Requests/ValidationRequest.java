package com.dscms.java_server.Requests;

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

    private MultipartFile nationalId;
    /*private MultipartFile bankStatement;
    private MultipartFile ursbCertificate;*/
}
