define(['express', 'database', 'table', 'user'], function(express, Database, Table, User) {
  var app = express();
  
  var getDatabase = function(req, type) {
    return User.getDatabaseAccessQuery(req.user, type)
      .then(function(hasAccess) {
        return Database.findOne({ $and: [{ _id: req.params.id }, hasAccess]}).exec();
      });
  };

  
  /* GET tables listing */
  
  app.get('/databases/:id/tables', function(req, res, next) {
        
    var getTables = function(database) {
      return Table.find({ databaseId: database._id }).exec();
    };

    getDatabase(req)
      .then(getTables)
      .then(res.send.bind(res), next);
  });

  
  /* Create Table */
  
  app.post('/databases/:id/tables', function(req, res, next) {
    
    var validateRemoteTable = function(database) {
      return database.query('SHOW TABLES LIKE ?', req.body.remoteTable)
        .then(function(result) {
          if (!result[0].length) {
            throw Error('Table does not exist');
          }
        });
    };

    var createTable = function() {
      return Table.create(req.body);
    };

    // Ignore databaseId value in request since that hasn't been validated
    req.body.databaseId = req.params.id;
    
    getDatabase(req, 'admin')
      .then(validateRemoteTable)
      .then(createTable)
      .then(res.send.bind(res), next);
  });

  
  /* GET single table */
  
  app.get('/databases/:id/tables/:tableId', function(req, res, next) {
    
    var getTable = function(database) {
      return Table.findOne({
        _id: req.params.tableId,
        databaseId: database._id
      }).exec();
    };
    
    getDatabase(req, 'admin')
      .then(getTable)
      .then(res.send.bind(res), next);
  });


  /* Update Table */
  
  app.put('/databases/:id/tables/:tableId', function(req, res, next) {
    
    var updateTable = function(database) {
      return Table.findOneAndUpdate({
        _id: req.params.tableId,
        databaseId: database._id
      }, req.body).exec();
    };
    
    getDatabase(req, 'admin')
      .then(updateTable)
      .then(res.send.bind(res), next);
  });


  /* Delete Table */

  app.delete('/databases/:id/tables/:tableId', function(req, res, next) {

    var deleteTable = function(database) {
      return Table.findOneAndRemove({
        _id: req.params.tableId,
        databaseId: database._id
      }).exec();
    };

    getDatabase(req, 'admin')
      .then(deleteTable)
      // TODO: Remove fields, etc.
      .then(res.send.bind(res), next);
  });

  return app;
});
