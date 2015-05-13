# EjectCar
EjectCar Source Code 
  
  
## Orverview, Architecture
Ejectカーです.  
見たことある人はわかると思います.  
OSCに出展する予定なので,ソースコード公開してます.  
とりあえず,おおざっぱな構成図は以下です．
![my_image](https://raw.githubusercontent.com/kamaboko123/EjectCar/master/WebClient/tech/system.png)
図中で「さくらVPS」と書かれているのは，あくまで開発者の環境なので適宜読み替えてください．  
サーバーはチャットサーバーになっており，WebクライアントとEjectカーのメッセージを中継します．  
EjectカーがNAPT配下などにあり，内向きのポートフォワーディング設定ができない場合も使えるようにするためです.  
（実際に運用する段階ではスマートフォンのテザリングか,モバイルWi-Fiを利用するため）  
  
  
## How to use
とりあえず，大きく分けて以下の3つに分かれてるので，それぞれ解説していきます．
* Server
* Car
* WebClient
  
  
### Server
メッセージ中継を行うチャットサーバーはnode.jsで動作しますので,node.jsが動作する環境を用意してください.  
また,socket.ioを利用しますので,インストールしてください.  
ver0.8.7で動作確認をして開発を進めています.以下のコマンドでインストールできます.  
ex) npm install socket.io@0.8.7  
  
実行環境が構築できたら,/Server/app.jsを起動します.  
ex1) node app.js  
ex2) nohup node app.js &  
無事に起動すれば成功です.  
  
  
### Car
Ejectカー側はJavaで書かれていますので,実行環境を用意してください.  
推奨はjdk1.8.0以降です.  
以下のライブリラリを使用します.  
* socket.io通信 [socket.io-java-client](https://github.com/Gottox/socket.io-java-client)  
* JSON関連 [org.json](http://www.json.org/java/index.html)  
socket.ioのライブリラリはantを使うと楽にjarにできます.  
JSONのライブリラリもコンパイル,アーカイブしてjarにしてください.  
classpathに追加してください.  
  
/Car/EjectorClient.javaをコンパイルします.  
ex) javac EjectorClient.java  
  
Javaのプログラム内で,光学ドライブの状態を取得したり,ledを光らせたりするため,外部プログラムを使用しています.
外部プログラムはCで書かれており,別途コンパイルが必要です.  
  
/Car/command_status/status.cをコンパイルします.(下記の例では出力される実行ファイル名を「eje_st」にしています)  
ex) gcc status.c -o eje_st  
  
/Car/led/led6.cをコンパイルします.
ex) gcc led6.c -o led6.c  
以下のライブリラリが必要ですので,事前にインストールしておいてください.
* [Wiring Pi](http://wiringpi.com)  
（そもそもLED出力が不要な場合はJavaのプログラムを改造して,当該部分を削除してください）  
  
これらの外部プログラムはJavaのProcessBuilderから実行されます.
/usr/bin等,JavaのProcessBuilderから呼び出し可能な場所にプログラムを配置してください.  
ex) cp eje_st /usr/bin/eje_st
    cp led6 /usr/bin/led6

Car側のプログラムはこれで準備完了です.  
起動します.第1引数に接続先サーバー,第2引数に使用する光学ドライブのデバイスファイルを指定します.  
ex) java EjectorClient example.com /dev/cdrom1  
  
また,第3引数はオプションで,「AdvanceNotification」という機能を利用できます.  
ex) java EjectorClient example.com /dev/cdrom1  AdvanceNotification  

このオプションが有効になっているとWebクライアントへ事前通知を行います.  
(通常は「Eject命令受理→Eject実行→状態通知」)  
処理は「Eject命令受理→事前通知→Eject実行」となり,Ejectによるタイムロスの軽減ができます.  
Eject実行後には再通知が行われます.  
このオプションはEjectが常に成功することを前提として実装しているため,正しい事前通知が行われることは保証しません.  
例えば,事前通知を行った後,Ejectが実行され,そのEject処理が失敗した場合などは,正しい結果が返りません.  
(事後通知があるため,そこで事後通知で正しい結果を得ることが出来ます)  
よくわからない場合は,とりあえず第3引数に「AdvanceNotification」を指定しておくといいでしょう.  
  
  
### WebClient
/WebClinet のファイルを利用してください.  
以下のライブラリを使用しますので,/WebClient配下に置きます.  
* [jQuery](http://jquery.com/)
* [CreateJS](http://www.createjs.com/#!/CreateJS)
* [Socket.IO](http://socket.io)
※socket.ioのバージョンはServer側で利用するnode.jsのsocket.ioバージョンと揃えてください.
