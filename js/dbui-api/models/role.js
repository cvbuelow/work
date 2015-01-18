define(['mongoose'], function(mongoose) {
  
  var Schema = mongoose.Schema;
  var Role = new Schema({
    name: String,
    database: { type: Schema.Types.ObjectId, ref: 'Database' }
  });

  return mongoose.model('Role', Role);
});