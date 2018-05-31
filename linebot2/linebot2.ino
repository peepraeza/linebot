#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
#include <DNSServer.h>
#include <ESP8266WebServer.h>
#include <WiFiManager.h>     //https://github.com/tzapu/WiFiManager

#define D0 2             // USER LED Wake
#define ledPin  D0        // the number of the LED pin
#define Button 16
#define ConfigWiFi_Pin Button 
#define ESP_AP_NAME "ESP8266 Config AP"
#include <Wire.h>
#include "SSD1306.h" 
//const char* ssid     = "Peeja-WIFI"; //change this to your SSID
//const char* password = "p12345678"; //change this to your PASSWORD
const char* host = "http://test4embedded.herokuapp.com/bot.php";//change this to your linebot server ex.http://numpapick-linebot.herokuapp.com/bot.php

SSD1306  display(0x3c, D1, D2); // D1 = GPIO04, D2 = GPIO05
 
WiFiClient client;
int led = 14;
int check = 0;
const int trigPin = 13;
const int echoPin = 12;

void setup() {
  Serial.begin(115200);
  display.init();
  display.drawString(0, 0, "Don't Parking");
  display.display();
   pinMode(ledPin, OUTPUT);
  pinMode(ConfigWiFi_Pin,INPUT_PULLUP);
   pinMode(trigPin, OUTPUT); // Sets the trigPin as an Output
    pinMode(echoPin, INPUT); // Sets the echoPin as an Input
    Serial.println("Starting...");
    pinMode(led, OUTPUT);

  
  digitalWrite(ledPin,LOW);//Turn on the LED
  WiFiManager wifiManager;
  if(digitalRead(ConfigWiFi_Pin) == LOW) // Press button
  {
    wifiManager.resetSettings(); // go to ip 192.168.4.1 to config
  }
  wifiManager.autoConnect(ESP_AP_NAME); 
  while (WiFi.status() != WL_CONNECTED) 
  {
     delay(250);
     Serial.print(".");
  }
  Serial.println("WiFi connected");  
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());
  digitalWrite(ledPin,HIGH);
  
   
   
//    if (WiFi.begin(ssid, password)) {
//        while (WiFi.status() != WL_CONNECTED) {
//            delay(500);
//            Serial.print(".");
//        }
//    }
//    Serial.println("WiFi connected");
//    Serial.println("IP address: ");
//    Serial.println(WiFi.localIP());
    
    digitalWrite(led, 0);   
    send_json("notcar");
}

void send_json(String data){
  
    StaticJsonBuffer<300> JSONbuffer;   //Declaring static JSON buffer
    JsonObject& root = JSONbuffer.createObject(); 
    JsonObject& esp = root.createNestedObject("ESP"); 
    
    esp["device"] = WiFi.macAddress();
    esp["status"] = data;

    char JSONmessageBuffer[300];
    root.prettyPrintTo(JSONmessageBuffer, sizeof(JSONmessageBuffer));
    Serial.println(JSONmessageBuffer);
 
    HTTPClient http;    //Declare object of class HTTPClient
 
    http.begin(host);      //Specify request destination
    http.addHeader("Content-Type", "application/json");  //Specify content-type header
 
    int httpCode = http.POST(JSONmessageBuffer);   //Send the request
    String payload = http.getString();                                        //Get the response payload
 
    Serial.println(httpCode);   //Print HTTP return code
    Serial.println(payload);    //Print request response payload
 
    http.end();  //Close connection
}
void wait_user(){
    String url = "http://test4embedded.herokuapp.com/db.json"; //ไม่ต้องเปลี่ยน สำหรับทดลอง
    Serial.println();
    HTTPClient http;
    http.begin(url);
    int httpCode = http.GET();
    if (httpCode == 200) {
      const size_t bufferSize = JSON_OBJECT_SIZE(2) + JSON_OBJECT_SIZE(3) + JSON_OBJECT_SIZE(5) + JSON_OBJECT_SIZE(8) + 370;
      DynamicJsonBuffer jsonBuffer(bufferSize);
      JsonObject& root = jsonBuffer.parseObject(http.getString());
      // Parameters
       String content = root["event"][WiFi.macAddress()]["status"]; // "Leanne Graham"
      
      Serial.println(content);
      
      if (content == "wait"){
          Serial.print("\nAwaiting Command\n");    
      } 
      else if (content == "yes"){
          digitalWrite(led,  0);
          check = 2;
          Serial.print("\nSay Yes\n"); 
          send_json("ready");   
          display.init();
          display.drawString(0, 0, "Don't Parking");
          display.display();
      }
      else if (content == "no"){
          check = 2;
          Serial.print("\nSay No\n");   
          send_json("ready");
      }else{
          Serial.print("\nerror\n");
      }
    } else {
       Serial.println("Fail. error code " + String(httpCode));
    }
    delay(1000);
}
float get_distance() {
    long duration;
    int distance;
    digitalWrite(trigPin, LOW);
    delayMicroseconds(2);

    digitalWrite(trigPin, HIGH);
    delayMicroseconds(10);
    digitalWrite(trigPin, LOW);

    duration = pulseIn(echoPin, HIGH);
    distance= duration*0.034/2;

    Serial.print("Distance: ");
    Serial.println(distance);
    return distance;
}
void loop() {
    digitalWrite(D0, LOW);  // turn off the LED 
     delay(500);
    digitalWrite(D0, HIGH);  // turn off the LED  
    float distance = get_distance();
    if(check==0){
      while(distance > 7){
        distance = get_distance();
        if(distance <= 7){
          delay(10000);
          distance = get_distance();
        }
        delay(300);
      }
      display.init();
      display.drawString(0, 0, "Get Out!!");
      display.display(); 
      digitalWrite(led, 1);
      check = 1;
      send_json("wait");
    }else if(check==1){
      wait_user();
      if(distance > 7){
        delay(10000);
        distance = get_distance();
        check=2;
      }
    }else{
      if(distance > 7){
        delay(10000);
        distance = get_distance();
        if(distance > 7){
          check = 0;
          send_json("carout");
          Serial.print("\nCar Out\n");  
          digitalWrite(led, 0);
          display.init();
          display.drawString(0, 0, "Don't Parking");
          display.display(); 
        }
      }
    }   
    delay(300);  
}
