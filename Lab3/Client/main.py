import sys, urllib3


#GET - чтение файла; 2 параметра - откуда, куда
#PUT - перезапись файла; 2 параметра - откуда, куда
#POST - добавление в конец файла; 2 параметра - откуда, куда
#DELETE - удаление файла; 1 параметр откуда
#COPY - копирование файла; 2 параметра - откуда, куда
#MOVE - перемещение файла; 2 параметра - откуда, куда
#FILES - список файлов

BUFFER_SIZE = 10485760


url = 'SERVER_URL'

http = urllib3.PoolManager()


method = ''
departure = ''
destination = ''

methods = ['GET', 'PUT', 'POST', 'DELETE', 'COPY', 'MOVE', 'FILES']

HTTP_RESPONSES = {

        200: 'OK',
        220: 'Successfully Rewritten',
        221: 'Successfully Added',
        222: 'Successfully Deleted',
        223: 'Successfully Copied',
        224: 'Successfully Moved',

        404: 'File Not Found',
        444: 'File Not Specified',
        445: 'Second File Not Specified',

        520: 'Rewriting Error',
        521: 'Adding Error',
        522: 'Delete Error',
        523: 'Copy Error',
        524: 'Move Error',

    }

if len(sys.argv) > 1:
        method = sys.argv[1].upper()
        if len(sys.argv) == 3:
                departure = sys.argv[2].replace('/', ':')
        else:
                if len(sys.argv) > 3:
                        departure = sys.argv[2].replace('../', '').replace('/', ':')
                        destination = sys.argv[3].replace('../', '').replace('/', ':')
else:
        exit(0)


if method not in methods:
        print('Method not found')
        exit(0)

if method == 'FILES':

        r = http.request(method, url)
        if r.status == 200:
                print(r.data.decode("utf-8"))
        else:
                print(HTTP_RESPONSES[r.status])

        exit(0)

if method in ['DELETE', 'COPY', 'MOVE']:

        url = url + departure + '/' + destination
        r = http.request(method, url)
        print(HTTP_RESPONSES[r.status])

        exit(0)

if method == 'PUT':
        url = url + destination + '/'
        f = open(departure, "rb")
        while True:
                data = f.read(BUFFER_SIZE)
                if not data:
                        break
                r = http.request(method, url, body=data)
                method = 'POST'

        print(HTTP_RESPONSES[r.status])
        f.close()

        exit(0)

if method == 'POST':
        url = url + destination + '/'
        f = open(departure, "rb")
        while True:
                data = f.read(BUFFER_SIZE)
                if not data:
                        break
                r = http.request(method, url, body=data)

        print(HTTP_RESPONSES[r.status])
        f.close()

        exit(0)

if method == 'GET':

        url = url + departure + '/'

        r = http.request(method, url)

        if r.status != 200:
                print(HTTP_RESPONSES[r.status])
                exit(0)

        f = open(destination, "wb")
        f.write(r.data)
        f.close()

        repeats = int(r.headers['Blocks'])
        f = open(destination, "ab")

        i = 1
        while i < repeats:
                i += 1
                r = http.request(method, url, headers={'Current': i})
                if r.status == 200:
                        f.write(r.data)
                else:
                        i -= 1

        print(HTTP_RESPONSES[r.status])
        f.close()

        exit(0)
