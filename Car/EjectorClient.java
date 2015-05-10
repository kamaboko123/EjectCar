import io.socket.*;
import org.json.*;
import java.util.*;
import java.io.*;

class EjectorClient{
	public static void main(String args[]){
		try{
			String server = args[0];
			String device = args[1];
			
			/*事前通知（試験的実装）
			  このオプションが有効になっているとWebクライアントへ事前通知を行います。
			  処理は「Eject命令受理→事前通知→Eject実行」となり、Ejectによるタイムロスの軽減ができます。
			  Eject実行後には再通知が行われます。
			  このオプションはEjectが常に成功することを前提として実装しているため、正しい事前通知が行われることは保証しません。
			  例えば、事前通知を行った後、Ejectが実行され、そのEject処理が失敗した場合などは、正しい結果が返りません。
			  
			  利用する場合はEjectorClient起動時にコマンドライン引数の第三引数に「AdvanceNotification」を指定します。
			*/
			boolean AdvanceNotification;
			if(args.length == 3 && args[2].equals("AdvanceNotification")){
				AdvanceNotification = true;
				System.out.println("Advance Notification Enable!!");
			}else{
				AdvanceNotification = false;
			}
			
			
			SocketClient client = new SocketClient(server, device, AdvanceNotification);
		}
		catch(Exception e){
			e.printStackTrace();
		}
	}
}


class SocketClient{
	String server;
	String device;
	boolean AdvanceNotification;
	Action action;
	SocketIO socket;
	
	Status status;
	Timer timer;
	Eject eject;
	Counter counter;
	
	public SocketClient(String server, String device, boolean AdvanceNotification) throws Exception{
		this.server = server;
		this.device = device;
		this.AdvanceNotification = AdvanceNotification;
		this.action = new Action();
		socket = new SocketIO(server);
		status = new Status(socket, this.device);
		timer = new Timer(socket);
		eject = new Eject(this.device);
		counter = new Counter("./count.txt");
		
		socket.connect(action);
		status.start();
		timer.start();
		eject.start();
	}
	
	class Action implements IOCallback{
		@Override
   		 public void onMessage(JSONObject json, IOAcknowledge ack) {
			try{
				System.out.println("Server said:" + json.toString(2));
			}
			catch(Exception e){
				e.printStackTrace();
			}
		}
		
		@Override
		public void onMessage(String data, IOAcknowledge ack) {
			System.out.println("Server said: " + data);
		}
		
		@Override
		public void onError(SocketIOException socketIOException) {
			System.out.println("an Error occured");
			socketIOException.printStackTrace();
		}
		
		@Override
		public void onDisconnect() {
			System.out.println("Connection terminated.");
		}
		
		@Override
		public void onConnect() {
			//JSONObject obj = new JSONObject();
		}
		
		@Override
		public void on(String event, IOAcknowledge ack, Object... args) {
			try{
				if(event.equals("tryEject")){
					if(timer.eject()){
						socket.emit("Active", timer.getActiveData());
						
						if(AdvanceNotification){
							//事前通知
							status.advancedNotificationStatus();
						}
						
						//Eject実行
						eject.eject();
						counter.up();
						
						if(AdvanceNotification){
							//事前通知オプションが有効の場合、事後通知を行う
							status.notificationStatus();
						}
					}
				}
				else if(event.equals("getStatus")){
					status.sendExStatus(timer.getActiveData());
				}
			}
			catch(Exception e){
				e.printStackTrace();
			}
		}
		
		
	}
}

class Status extends Thread{
	ProcessBuilder pb;
	Integer status;
	SocketIO socket;
	
	public Status(SocketIO socket, String device) throws Exception{
		pb = new ProcessBuilder("eje_st", device);
		this.socket = socket;
		status = new Integer(getStatus());
	}
	
	public void run(){
		try{
			while(true){
				/*
				int st = getStatus();
				if(!status.equals(st)){
					status = new Integer(st);
					sendStatus(status);
				}
				*/
				notificationStatus();
				
				Thread.sleep(100);
			}
		}catch(Exception e){
			e.printStackTrace();
		}
	}
	
	public int getStatus() throws Exception{
		Process process = pb.start();
		return(process.waitFor());
	}
	
	public synchronized void notificationStatus() throws Exception{
		int st = getStatus();
		if(!status.equals(st)){
			System.out.println("!!");
			status = new Integer(st);
			sendStatus(status);
		}
	}
	
	public synchronized void advancedNotificationStatus() throws Exception{
		JSONObject obj;
		int st = getStatus();
		int notification = -1;
		if(st == 0){
			notification = 1;
		}
		else if(st == 1){
			notification = 0;
		}
		sendStatus(notification);
	}
	
	public synchronized void sendExStatus(JSONObject ActiveData) throws Exception{
		//ステータス通知
		sendStatus(getStatus());
		//Active通知
		socket.emit("Active", ActiveData);
	}
	
	public JSONObject makeStatusData(int StatusCode) throws Exception{
		JSONObject obj = new JSONObject();
		if(StatusCode == 0){
			obj.put("code", 1);
			obj.put("message", "Tray Close");
		}
		else if(StatusCode == 1){
			obj.put("code", 0);
			obj.put("message", "Tray Open");
		}
		else{
			obj.put("code", -1);
			obj.put("message", "Error!");
		}
		return(obj);
	}
	
	public synchronized void sendStatus(int StatusCode) throws Exception{
		JSONObject obj = makeStatusData(StatusCode);
		socket.emit("Status", obj);
	}
}

class Timer extends Thread{
	Calendar ejected;
	boolean ejectFlg = false;
	SocketIO socket;
	
	public Timer(SocketIO socket){
		ejected = Calendar.getInstance();
		this.socket = socket;
	}
	
	public void run(){
		try{
			while(true){
				if(ejectFlg == false){
					Calendar now_c = Calendar.getInstance();
					long now = now_c.getTimeInMillis();
					long last = ejected.getTimeInMillis();
					
					if(now - last >= 5000){
						ejectFlg = true;
						socket.emit("Active", getActiveData());
					}
				}
				Thread.sleep(100);
			}
		}catch(Exception e){
			e.printStackTrace();
		}
	}
	
	public boolean isActive(){
		return(ejectFlg);
	}
	
	public synchronized JSONObject getActiveData() throws Exception{
		JSONObject obj = new JSONObject();
		if(isActive()){
			obj.put("code", "1");
			obj.put("message", "Active");
		}else{
			obj.put("code", "0");
			obj.put("message", "not Active");
		}
		return(obj);
	}
	
	public synchronized boolean eject(){
		if(ejectFlg){
			ejected = Calendar.getInstance();
			ejectFlg = false;
			return(true);
		}
		else{
			return(false);
		}
	}
}


class Eject extends Thread{
	ProcessBuilder pb;
	boolean ejectFlg = false;
	
	public Eject(String device){
		pb = new ProcessBuilder("eject", "-T", device);
	}
	
	public void run(){
		while(true){
			try{
				if(ejectFlg){
					//System.out.println("eje");
					Process process = pb.start();
					process.waitFor();
					ejectFlg = false;
				}
				Thread.sleep(500);
			}
			catch(Exception e){
				e.printStackTrace();
			}
		}
	}
	
	public void eject() throws Exception{
		ejectFlg = true;
	}
	
}

class Counter{
	File file;
	
	public Counter(String file){
		this.file = new File(file);
	}
	
	public int get() throws Exception{
		FileReader reader = new FileReader(file);
		StringBuilder sb = new StringBuilder();
		int ch;
		while((ch = reader.read()) != -1){
			if(ch != 10){
				sb.append((char)ch);
			}
		}
		
		reader.close();
		
		System.out.println(sb.toString());
		return(Integer.valueOf(sb.toString()));
	}
	
	private void writeInt(int num) throws Exception{
		FileWriter writer = new FileWriter(file);
		Integer Num = new Integer(num);
		writer.write(Num.toString());
		writer.close();
		
		ProcessBuilder pb = new ProcessBuilder("led6", Num.toString());
		pb.start();
	}
	
	public void up() throws Exception{
		int count = get();
		count++;
		writeInt(count);
	}
}
