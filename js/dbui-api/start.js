var requirejs = require('requirejs');

requirejs.config({
  baseUrl: __dirname,
  nodeRequire: require,
  paths: {
    app: 'app',
    user: 'models/user',
    role: 'models/role',
    database: 'models/database',
    table: 'models/table',
    index: 'api/index',
    users: 'api/users',
    databases: 'api/databases',
    tables: 'api/tables'
  }
});

requirejs(['app', 'express-jwt', 'index', 'users', 'databases', 'tables'],
  function(app, expressJwt, index, users, databases, tables) {
    
    app.use('/databases', expressJwt({secret: 'secret'}));

    app.use(index);
    app.use(users);
    app.use(databases);
    app.use(tables);

    app.use(function(err, req, res, next) {
      console.error(err.stack);
      res.send(500, { message: err.message });
    });

    app.set('port', process.env.PORT || 3000);

    app.listen(app.get('port'), function() {
      console.log('Express server listening on port ' + app.get('port'));
    });
  });