define(['express', 'domain', 'express-session', 'morgan', 'cookie-parser', 'body-parser', 'mongoose', 'passport', 'passport-local', 'cors', 'user'],
  function(express, domain, session, logger, cookieParser, bodyParser, mongoose, passport, passportLocal, cors, User) {
    
    mongoose.connect('mongodb://localhost/dbui');
    var db = mongoose.connection;
    db.on('error', function () {
      throw new Error('unable to connect to database');
    });

    var app = express();

    app.use(function(req, res, next) {
      var reqDomain = domain.create();
      reqDomain.add(req);
      reqDomain.add(res);

      res.on('close', function() {
        reqDomain.dispose();
      });
      reqDomain.on('error', function(err) {
        next(err);
      });
      reqDomain.run(next);
    });

    app.use(logger('dev'));
    app.use(bodyParser.json());
    app.use(bodyParser.urlencoded());
    app.use(cookieParser());
    app.use(session({ secret: 'keyboard cat' }));
    app.use(passport.initialize());
    app.use(passport.session());
    app.use(cors({
      origin: 'http://localhost:9000'
    }));
    app.options('*', cors());
    
    // Passport Config

    var LocalStrategy = passportLocal.Strategy;

    passport.serializeUser(function(user, done) {
      done(null, user.id);
    });

    passport.deserializeUser(function(id, done) {
      User.findById(id, done);
    });

    passport.use(new LocalStrategy({ usernameField: 'email' }, function(email, password, done) {
      User.findOne({ email: email }, function(err, user) {
        if (err) return done(err);
        if (!user) return done(null, false);
        user.comparePassword(password, function(err, isMatch) {
          if (err) return done(err);
          if (isMatch) return done(null, user);
          return done(null, false);
        });
      });
    }));

    return app;
  });