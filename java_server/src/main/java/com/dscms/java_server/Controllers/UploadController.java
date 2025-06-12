package com.dscms.java_server.Controllers;

import net.sourceforge.tess4j.ITesseract;
import net.sourceforge.tess4j.Tesseract;
import net.sourceforge.tess4j.TesseractException;
import org.apache.pdfbox.pdmodel.PDDocument;
import org.apache.pdfbox.rendering.PDFRenderer;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.multipart.MultipartFile;

import javax.imageio.ImageIO;
import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;

@RestController
public class UploadController {

        @PostMapping("/doc")
    public ResponseEntity<?> fileUpload(@RequestParam("file") MultipartFile file){
        String path="D:/uploads/";
        try {
            File tempFile = File.createTempFile("doc", ".pdf");
            file.transferTo(tempFile);

            PDDocument doc= PDDocument.load(tempFile);

            PDFRenderer renderer= new PDFRenderer(doc);

            StringBuilder extractedText=new StringBuilder();

            for(int i=0; i< doc.getNumberOfPages(); i++){
                BufferedImage img= renderer.renderImageWithDPI(i, 300);

                File imageFile= File.createTempFile("page_",".png");
                ImageIO.write(img, "png", imageFile);

                ITesseract tesseract= new Tesseract();
                tesseract.setDatapath("C:/Tesseract-OCR/tessdata");
                tesseract.setLanguage("eng");

                String text=tesseract.doOCR(imageFile);

                extractedText.append(text).append("\n");
            }
            doc.close();

            String text= extractedText.toString();

            return ResponseEntity.ok().body(text);

        } catch (IOException | TesseractException e) {
            return  ResponseEntity.status(HttpStatus.valueOf(500)).body("Error processing file");
        }

        /*File dir= new File(path);
        if(!dir.exists()){
            dir.mkdirs();
        }

        try {
            file.transferTo(new File(path + file.getOriginalFilename()));
            return ResponseEntity.ok("Document uploaded successfully");
        } catch (IOException e) {
            return  ResponseEntity.status(HttpStatus.valueOf(500)).body("Error uploading file");
        }*/
    }

}
