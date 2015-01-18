define(['express'], function(express) {

  var app = express();

  /* GET home page. */
  app.get('/', function(req, res, next) {
    res.send('dbui api');
  });

  return app;
});
