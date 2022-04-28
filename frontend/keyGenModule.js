module.exports = { 
	KeyGen: function (){
		const { generateKeyPair } = require('crypto');
		generateKeyPair('rsa', {
		  modulusLength: 2048,    
		  publicExponent: 0x10101,
		  publicKeyEncoding: {
			type: 'spki', //simple public key infrastructure
			format: 'pem'
		  },
		  privateKeyEncoding: {
			type: 'pkcs1',
			format: 'pem',
		  }
		}, (err, publicKey, privateKey) => { // Callback function
			   if(!err)
			   {
				 // Prints new asymmetric key pair
				 //console.log("Public Key is : ", publicKey.toString('base64'));
				 console.log();
				 //console.log("Private Key is: ", privateKey);
				 const { writeFileSync } = require('fs');
				 writeFileSync('id_rsa_public.pem', publicKey);
				 writeFileSync('id_rsa_private.pem', privateKey);
			   }
			   else
			   {
				 // Prints error
				 console.log("Errr is: ", err);
			   }
				 
		  });
	}
}
//keyGenerator.KeyGen();