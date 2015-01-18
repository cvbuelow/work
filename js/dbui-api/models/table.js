define(['mongoose', 'mysql', 'q'], function(mongoose, mysql, q) {

  var Schema = mongoose.Schema;
  var Table = new Schema({
    databaseId: { type: Schema.Types.ObjectId, ref: 'Database', required: true },
    remoteTable: { type: String, required: true },
    displayName: { type: String, required: true }
  });

  return mongoose.model('Table', Table);
});