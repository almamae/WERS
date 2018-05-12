var http = require('http');
http.createServer(function (req, res) {
  res.writeHead(200, {'Content-Type': 'text/plain'});
  res.end('Hello World\n');
}).listen(9090, process.env.IP_ADDRESS);
console.log(`Server running at http://${process.env.IP_ADDRESS}:9090/`);