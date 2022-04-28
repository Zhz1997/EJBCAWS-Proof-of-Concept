var request = require('./CertRequestModule')
request.ServerStat();



const fs = require('fs');
const publicKey = fs.readFileSync("./id_rsa_public.pem", { encoding: "utf8" });
request.UserAct('PAQTUser1',publicKey, '1');
