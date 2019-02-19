import sys, urllib3


#GET - чтение файла; 2 параметра - откуда, куда
#PUT - перезапись файла; 2 параметра - откуда, куда
#POST - добавление в конец файла; 2 параметра - откуда, куда
#DELETE - удаление файла; 1 параметр откуда
#COPY - копирование файла; 2 параметра - откуда, куда
#MOVE - перемещение файла; 2 параметра - откуда, куда
#FILES - список файлов


url = 'url'

http = urllib3.PoolManager()


method = ''
departure = ''
destination = ''

methods = ['GET', 'PUT', 'POST', 'DELETE', 'COPY', 'MOVE', 'FILES']

if len(sys.argv) > 1:
        method = sys.argv[1]
        method = method.upper()
        if (method == 'DELETE') and (len(sys.argv) > 2):
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
                print('Folder Not Specified')

        exit(0)

if method in ['DELETE', 'COPY', 'MOVE']:
        url = url + departure + '/' + destination
        r = http.request(method, url)
        if r.status == 200:
                print('Ready!')
        else:
                print('Sorry. Some errors: ' + str(r.status))

        exit(0)

if method in ['PUT', 'POST']:


        exit(0)
        
f = open('test.txt', "rb")
while True:
        data = f.read(10485760)
        if not data:
                break
        print(data)
