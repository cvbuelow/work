define(['mongoose', 'bcryptjs'], function(mongoose, bcrypt) {

  var Schema = mongoose.Schema;
  var User = new Schema({
    email: {
      type: String,
      unique: true,
      required: true
    },
    password: {
      type: String,
      required: true
    },
    roles: [{ type: Schema.Types.ObjectId, ref: 'Role' }]
  });

  User.pre('save', function(next) {
    var user = this;
    if (!user.isModified('password')) {
      return next();
    }
    bcrypt.genSalt(10, function(err, salt) {
      if (err) return next(err);
      bcrypt.hash(user.password, salt, function(err, hash) {
        if (err) return next(err);
        user.password = hash;
        next();
      });
    });
  });

  User.methods.comparePassword = function(candidatePassword, cb) {
    bcrypt.compare(candidatePassword, this.password, function(err, isMatch) {
      if (err) return cb(err);
      cb(null, isMatch);
    });
  };

  var model = mongoose.model('User', User);

  model.getDatabaseAccessQuery = function(user, type) {
    
    var roles = { path: 'roles' };
    if (type) {
      roles.match = { name: type };
    }

    return model.findById(user._id, 'roles').populate(roles).exec()
      .then(function(u) {
        return { $or: [
          { owner: u._id },
          { _id: { $in: u.roles.map(function(role) {
              return role.database;
            })}
          }
        ]};
      });
  };

  return model;
});