var api = new restfulAPI('http://somesite.com');

api.addAuthUser('some_user');
api.passwordToSecret('somepass');

var response = api.request('GET', '/api/initiate/'); // {'z':'here', 'a':'I', 'm':'am'}

response.then(function(data){
    console.log(data);
}).catch(function(xhr) {
    console.log(xhr);
});