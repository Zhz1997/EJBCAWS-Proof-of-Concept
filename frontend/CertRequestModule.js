module.exports = {
	ServerStat: function (){
		const https = require('http');
		const GetReqURL = 'http://localhost:8000/api/verify';
		https.get(GetReqURL, (resp) => {

			console.log('Https response code: ', resp.statusCode);
			//console.log('Server response: ', resp.body);
			resp.on('data', (d) => {
				process.stdout.write(d);
			});

		}).on("error", (err) => {
			console.log("Error: " + err.message);
		});
		console.log('');
	},
	
	UserAct: function (username, publicKey, stat){
		const https = require('http');
		
		const postData = JSON.stringify({
			'username': username,
			'publickey': publicKey,
			'userstat': stat,
		});
		const options = {
			hostname: "127.0.0.1",
			port:"8000",
			path: '/api/certReq',
			method: 'POST',
			json: postData,
			headers: {
			'Content-Type': 'application/json',
			'Content-Length': postData.length
			}
		};
		var req = https.request(options, (res) => {
			console.log('statusCode:', res.statusCode);
			console.log('headers:', res.headers);

			res.on('data', (d) => {
				process.stdout.write(d);
			});
		});

		
		req.write(postData);
		console.log('Req body ', JSON.stringify(req.body));
		req.end();
		console.log('');
		req.on('error', (e) => {
			console.error(e);
		});
		
		//console.log('Testing: JSON body: ');
		//console.log('%j', postData);
		
	}
}