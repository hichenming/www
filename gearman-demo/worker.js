var Gearnode = require("gearnode");
var io = require('socket.io').listen(8000);
io.set('browser client minification', true);
io.sockets.on('connection', function(socket){
    socket.on('chat', function(data){
        socket.broadcast.emit('chat', {
            'msg': data
        });
    });
});

worker = new Gearnode();
worker.addServer(); // use localhost

worker.addFunction("sendmsg", "utf-8", function(payload, job){
    var response =  payload.toUpperCase();
    io.sockets.emit('chat', {'msg':response});
    job.complete(response);
});
