from bottle import route, run

@route ('/hello')
def hello():
	return "<h1>Hello</h1>"

##run(host='192.30.162.36', port=8080)

run(host='0.0.0.0')
