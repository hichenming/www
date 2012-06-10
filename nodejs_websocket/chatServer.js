
var io = require('socket.io').listen(8000);

io.sockets.on('connection', function (socket){
	socket.on('chat', function(data){
		var sender = 'unregistered';
		socket.get('nickname', function(err, name){
			console.log('chat message by ', name);
			console.log('error', err);
			sender = name;
		});

		socket.broadcast.emit('chat', {
			msg: data,
			msgr: sender
		});
	});

	socket.on('register', function(name){
		socket.set('nickname', name, function(){
			socket.broadcast.emit('chat', {
				msg: 'naay nag apil2! si '+ name + '!',
				msgr: 'mr. server'
			});
		});
	});
});
