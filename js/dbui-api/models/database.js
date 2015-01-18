define(['events', 'mongoose', 'tunnel-ssh', 'mysql', 'q'], function(events, mongoose, Tunnel, mysql, q) {

  var eventEmitter = new events.EventEmitter();
  var Schema = mongoose.Schema;
  var Database = new Schema({
    owner: { type: Schema.Types.ObjectId, ref: 'User', required: true }, //person who pays for it
    hostname: { type: String, default: '127.0.0.1' },
    username: { type: String, required: true },
    password: { type: String, required: true },
    name: { type: String, required: true },
    port: { type: String, default: '3306' },
    driver: String,
    charSet: String,
    collat: String,
    sshHost: String,
    sshPort: { type: String, default: '22' },
    sshUser: String,
    sshPass: String,
    sshKey: String,
    displayName: { type: String, required: true },
    config: String
  });
  var mysqlConnection;

  Database.methods.connect = function() {

    var db = this;

    var tunnel = new Tunnel({
      remoteHost: db.hostname,
      remotePort: db.port,
      verbose: true, // dump information to stdout
      sshConfig: {
        host: db.sshHost,
        port: db.sshPort,
        username: db.sshUser,
        password: db.sshPass
      }
    });

    var connectToMySQL = function (address) {

      mysqlConnection = mysql.createConnection({
        host: address.address,  //not sure this is right...might need to be db.hostname
        database: db.name,
        user: db.username,
        password: db.password,
        port: address.port,
        insecureAuth: true
      });
      return q.ninvoke(mysqlConnection, 'connect');
    };

    // eventEmitter.on('close-tunnel', tunnel.close.bind(tunnel));

    return tunnel.connect()
      .then(connectToMySQL);
  };

  /* might not need to ever close the tunnel...it appears to close itself */
  Database.methods.disconnect = function() {
    eventEmitter.emit('close-tunnel');
  };
  
  Database.methods.query = function() {
    var args = arguments;
    var queryMySQL = function() {
      return q.npost(mysqlConnection, 'query', args);
    };

    return this.connect()
      .then(queryMySQL);
  };

  return mongoose.model('Database', Database);
});