#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
const char* ssid     = "Peeja-WIFI"; //change this to your SSID
const char* password = "p12345678"; //change this to your PASSWORD
const char* host = "http://test4embedded.herokuapp.com/bot.php";//change this to your linebot server ex.http://numpapick-linebot.herokuapp.com/bot.php

WiFiClient client;
int led = 14;
int check = 0;
const int trigPin = 13;
const int echoPin = 12;

void setup() {
    pinMode(trigPin, OUTPUT); // Sets the trigPin as an Output
    pinMode(echoPin, INPUT); // Sets the echoPin as an Input
    Serial.begin(115200);
    Serial.println("Starting...");
    pinMode(led, OUTPUT);
   
    if (WiFi.begin(ssid, password)) {
        while (WiFi.status() != WL_CONNECTED) {
            delay(500);
            Serial.print(".");
        }
    }
    Serial.println("WiFi connected");
    Serial.println("IP address: ");
    Serial.println(WiFi.localIP());
    
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
    String url = "http://test4embedded.herokuapp.com/testfile.txt"; //ไม่ต้องเปลี่ยน สำหรับทดลอง
    Serial.println();
    HTTPClient http;
    http.begin(url);
    int httpCode = http.GET();
    if (httpCode == 200) {
      String content = http.getString();
      Serial.println(content);

      int Valueyes = -1;
      int Valueno = -1;   
      Valueyes = content.indexOf("yes");
      Valueno = content.indexOf("no");
      
      if (Valueyes and Valueno == -1){
          Serial.print("\nAwaiting Command\n");    
      } 
      if (Valueyes != -1){
          digitalWrite(led,  0);
          check = 2;
          Serial.print("\nSay Yes\n"); 
          send_json("ready");    
      }
      if (Valueno != -1){
          check = 2;
          Serial.print("\nSay No\n");   
          send_json("ready");
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
        }
      }
    }   
    delay(300);  
}
