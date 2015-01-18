define(['express', 'passport', 'jsonwebtoken', 'user'], function(express, passport, jwt, User) {
  var app = express();


  app.post('/login', passport.authenticate('local'), function(req, res) {
    delete req.user.password;
    var token = jwt.sign(req.user, 'secret', { expiresInMinutes: 60*5 });
    res.send({
      token: token,
      email: req.user.email
    });
  });


  app.get('/logout', function(req, res, next) {
    // need to destroy the jwt here...
    req.logout();
    res.send(200);
  });


  app.post('/users', function(req, res, next) {
    
    var user = new User({
      email: req.body.email,
      password: req.body.password
    });
    
    user.save(function(err) {
      if (err) { return next(err); }
      res.send(200);
    });

  });

  return app;
});
