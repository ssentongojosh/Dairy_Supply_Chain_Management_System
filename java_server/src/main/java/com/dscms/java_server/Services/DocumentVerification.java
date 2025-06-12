package com.dscms.java_server.Services;

import net.sourceforge.tess4j.ITesseract;
import net.sourceforge.tess4j.Tesseract;
import net.sourceforge.tess4j.TesseractException;
import org.apache.pdfbox.pdmodel.PDDocument;
import org.apache.pdfbox.rendering.PDFRenderer;
import org.springframework.web.multipart.MultipartFile;

import javax.imageio.ImageIO;
import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;

public interface DocumentVerification {

    static String extractText(MultipartFile file){

        try {

            //Store a temporary file
            File tempFile = File.createTempFile("upload", ".pdf");

            //Transfer the pdf file to the tempFile memory
            file.transferTo(tempFile);

            //Load the pdf uploaded
            PDDocument document = PDDocument.load(tempFile);

            //Create a PDF render object that will turn the pdf to image
            PDFRenderer renderer = new PDFRenderer(document);

            //Create a StringBuilder object to build a string of the extracted text
            StringBuilder extractedText =  new StringBuilder();

            //Loop through every page to Turn the whole pdf to image
            for(int i=0; i< document.getNumberOfPages(); i++){

                //Turn each page into image
                BufferedImage img = renderer.renderImageWithDPI(i, 600);

                //Create a temporary file
                File imgFile = File.createTempFile("page_", ".png");

                //Save the image created in the imgFile memory
                ImageIO.write(img, "png", imgFile);

                ITesseract tesseract = new Tesseract();
                tesseract.setDatapath("C:/Tesseract-OCR/tessdata");
                tesseract.setLanguage("eng");

                String text = tesseract.doOCR(imgFile);

                extractedText.append(text).append("\n");
            }

            document.close();

            return extractedText.toString();

        } catch (IOException | TesseractException e) {
            throw new RuntimeException(e);
        }


    }

}
