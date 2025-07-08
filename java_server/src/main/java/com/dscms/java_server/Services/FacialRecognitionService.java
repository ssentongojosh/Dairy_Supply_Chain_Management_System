package com.dscms.java_server.Services;

import org.bytedeco.javacpp.DoublePointer;
import org.bytedeco.javacpp.IntPointer;
import org.bytedeco.javacv.CanvasFrame;
import org.bytedeco.javacv.Frame;
import org.bytedeco.javacv.Java2DFrameConverter;
import org.bytedeco.javacv.OpenCVFrameGrabber;
import org.bytedeco.opencv.global.opencv_core;
import org.bytedeco.opencv.opencv_core.MatVector;
import org.bytedeco.opencv.opencv_face.LBPHFaceRecognizer;
import org.opencv.core.*;
import org.opencv.highgui.HighGui;
import org.opencv.imgcodecs.Imgcodecs;
import org.opencv.imgproc.Imgproc;
import org.opencv.objdetect.CascadeClassifier;
import org.opencv.videoio.VideoCapture;
import org.opencv.videoio.Videoio;
import org.springframework.stereotype.Service;



import java.awt.image.BufferedImage;
import java.io.File;

@Service
public class FacialRecognitionService {

  private static boolean openCVLoaded = false;

  static {
    loadOpenCV();
  }

  private static synchronized void loadOpenCV() {
    if (!openCVLoaded) {
      try {
        System.loadLibrary(Core.NATIVE_LIBRARY_NAME);
        openCVLoaded = true;
        System.out.println("OpenCV library loaded successfully");
      } catch (UnsatisfiedLinkError e) {
        // Library might already be loaded
        if (e.getMessage().contains("already loaded")) {
          openCVLoaded = true;
          System.out.println("OpenCV library was already loaded");
        } else {
          System.err.println("Failed to load OpenCV library: " + e.getMessage());
          throw e;
        }
      }
    }
  }

  public static Mat detectAndCropFace(String imagePath) {
    String cascadePath = "Data/haarcascade_frontalface_alt.xml";
    CascadeClassifier faceDetector = new CascadeClassifier(cascadePath);
    //faceDetector.load(cascadePath);

    if (!faceDetector.load(cascadePath)) {
      System.err.println("Error: Could not load cascade classifier");
      return null; // or throw exception
    }

    Mat image = Imgcodecs.imread(imagePath);
    Mat gray = new Mat();
    Imgproc.cvtColor(image, gray, Imgproc.COLOR_BGR2GRAY);

    MatOfRect faceDetections = new MatOfRect();
    faceDetector.detectMultiScale(gray, faceDetections);



    for (Rect rect : faceDetections.toArray()) {
      //Imgproc.rectangle(frame, new Point(rect.x, rect.y), new Point(rect.x + rect.width, rect.y + rect.height), new Scalar(0, 255, 0),2);
      Mat frame = new Mat(gray, rect);
      //Imgcodecs.imwrite("ID image.jpg", frame);
      System.out.println("Face detected");
      return frame; // Crop and return grayscale face
    }

    return null; // No face found
  }

  public static Mat captureFace() {
    System.out.println("Attempting to open camera");
    VideoCapture camera = new VideoCapture(0, Videoio.CAP_DSHOW); // 0 = default webcam
    System.out.println("Proceeding...");

    /*if (!camera.isOpened()) {
      System.out.println("❌ Cannot open the camera.");
      return null;
    }*/

    CascadeClassifier faceDetector = new CascadeClassifier("Data/haarcascade_frontalface_default.xml");

    Mat frame = new Mat();
    boolean faceCaptured = false;

    while (!faceCaptured) {
      camera.read(frame); // Grab a frame

      if (frame.empty()) continue;

      Mat gray = new Mat();
      Imgproc.cvtColor(frame, gray, Imgproc.COLOR_BGR2GRAY);

      MatOfRect faces = new MatOfRect();
      faceDetector.detectMultiScale(gray, faces);

      for (Rect face : faces.toArray()) {
        if (face.width > 100 && face.height > 100) {
          // Crop and save grayscale face
          Mat faceROI = new Mat(gray, face);
          //Imgcodecs.imwrite("Capture.jpg", faceROI);
          //System.out.println("✅ Face saved at: " + outputPath);
          return faceROI;
        }
      }

      // Display live video in a popup window
      HighGui.imshow("Live Camera Feed", frame);
      if (HighGui.waitKey(30) == 27) break; // ESC key to manually exit
    }

    camera.release(); // Release webcam
    return null;
  }

  public static boolean captureFaceViaExternalApp() {
    try {
      ProcessBuilder builder = new ProcessBuilder(
        "java", "-Djava.library.path=C:/opencv/build/java/x64", "-jar", "FaceCaptureApp.jar"
      );
      builder.directory(new File("C:/xampp/htdocs/Dairy_Supply_Chain_Management_System/java_server/face-capture/out/artifacts/face_capture_jar")); // optional
      builder.inheritIO(); // to show console output

      Process process = builder.start();
      int exitCode = process.waitFor();
      return exitCode == 0;
    } catch (Exception e) {
      e.printStackTrace();
      return false;
    }
  }

  public static boolean compareFaces(Mat idFace, Mat liveFace) {
    LBPHFaceRecognizer recognizer = LBPHFaceRecognizer.create();

    // Training with ID image
    MatVector images = new MatVector(1);
    images.put(0, toBytedecoMat(idFace));
    org.bytedeco.opencv.opencv_core.Mat labels = new org.bytedeco.opencv.opencv_core.Mat(1, 1, opencv_core.CV_32SC1);
    labels.ptr(0).putInt(1);
    recognizer.train(images, labels);

    // Predict with live face
    IntPointer label = new IntPointer(1);
    DoublePointer confidence = new DoublePointer(1);
    recognizer.predict(toBytedecoMat(liveFace), label, confidence);

    System.out.println("Label: " + label.get() + " Confidence: " + confidence.get());
    System.out.println(label.get() == 1 && confidence.get() < 60);
    return label.get() == 1 && confidence.get() < 60;
  }

  private static org.bytedeco.opencv.opencv_core.Mat toBytedecoMat(Mat javaMat) {
    byte[] data = new byte[(int) (javaMat.total() * javaMat.channels())];
    javaMat.get(0, 0, data);
    org.bytedeco.opencv.opencv_core.Mat bytedecoMat = new org.bytedeco.opencv.opencv_core.Mat(
      javaMat.rows(), javaMat.cols(), javaMat.type()
    );
    bytedecoMat.data().put(data);
    return bytedecoMat;
  }

}
