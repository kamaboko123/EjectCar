#include <stdio.h>
#include <wiringPi.h>

/* GPIOポートの定義 */
#define LED_1 3 /* GPIO3 */
#define LED_2 4 /* GPIO4 */
#define LED_3 17 /* GPIO17 */
#define LED_4 27 /* GPIO27 */
#define LED_5 22 /* GPIO22 */
#define LED_6 10 /* GPIO10 */

/* ビット演算用の定義 */
#define MAX_COUNT 0x3f
#define BIT_1 0x01
#define BIT_2 0x02
#define BIT_3 0x04
#define BIT_4 0x08
#define BIT_5 0x10
#define BIT_6 0x20

/* 関数群 */
void ledAllClear(); /* 全部のLEDを消灯 */
void seg6LedOn(int num); /* 6セグLEDで整数を表示(0-63まで) */

int main(int argc, char *argv[]){
    int i;
    int data;
    
    //wiringPiのセットアップ
    if (wiringPiSetupGpio() == -1) return 1;
    
    //ピンのモードの設定
    pinMode(LED_1, OUTPUT);
    pinMode(LED_2, OUTPUT);
    pinMode(LED_3, OUTPUT);
    pinMode(LED_4, OUTPUT);
    pinMode(LED_5, OUTPUT);
    pinMode(LED_6, OUTPUT);
    
    //点灯処理
    for (i = 1; i < argc; i++){
        ledAllClear();
        //delay(500);
        sscanf(argv[i], "%d", &data);
        data = data & MAX_COUNT;    /* MAX_COUNTとの論理積をとる */
        seg6LedOn(data);
        //delay(500);
    }
    return 0;
}

void ledAllClear(void){
    digitalWrite(LED_1, 0);
    digitalWrite(LED_2, 0);
    digitalWrite(LED_3, 0);
    digitalWrite(LED_4, 0);
    digitalWrite(LED_5, 0);
    digitalWrite(LED_6, 0);
}

void seg6LedOn(int num){
    if ((num & BIT_1) == BIT_1){
        digitalWrite(LED_1, 1);
    }
    if ((num & BIT_2) == BIT_2){
        digitalWrite(LED_2, 1);
    }
    if ((num & BIT_3) == BIT_3){
        digitalWrite(LED_3, 1);
    }
    if ((num & BIT_4) == BIT_4){
        digitalWrite(LED_4, 1);
    }
    if ((num & BIT_5) == BIT_5){
        digitalWrite(LED_5, 1);
    }
    if ((num & BIT_6) == BIT_6){
        digitalWrite(LED_6, 1);
    }
}
