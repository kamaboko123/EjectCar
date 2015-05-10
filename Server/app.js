var socketio = require("socket.io");
var io = socketio.listen(process.env.VMC_APP_PORT || 3000);	

io.sockets.on("connection", function (socket) {
		
	socket.on("tryEject", function(data){
		io.sockets.emit("tryEject", {value:data.value})
	});
	
	socket.on("getStatus", function(data){
		io.sockets.emit("getStatus", {value:data.value})
	});

	socket.on("Status", function(data){
		io.sockets.emit("Status", {code:data.code, message:data.message});
	});
	
	socket.on("Receive", function(data){
		io.sockets.emit("Receive", {value:data.value});
	});
	
	socket.on("Accept", function(data){
		io.sockets.emit("Accept", {message:data.message});
	});
	
	socket.on("Active", function(data){
		io.sockets.emit("Active", data);
	});
  /*	
	socket.on("disconnect", function () {
		//count--;
		//io.broadcast(count);
		//io.sockets.emit("S_to_C_message", {value:"user disconnected"});
	});*/
	
});
