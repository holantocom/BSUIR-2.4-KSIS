import os, socket, struct, re, sys


def toInt(addr):
    return struct.unpack("!I", socket.inet_aton(addr))[0]

def toStr(addr):
    return socket.inet_ntoa(struct.pack("!I", addr))

#$ networksetup -listallhardwareports
#netifaces.interfaces() ['lo0', 'gif0', 'stf0', 'XHC20', 'en0', 'p2p0', 'awdl0', 'en1', 'bridge0', 'utun0', 'utun1']

eths = ['en0', 'en1', 'en2', 'en3', 'en4', 'en5']

for eth in eths:

    #ip = toInt(socket.gethostbyname(socket.gethostname()))
    ip = os.popen('ipconfig getifaddr ' + eth).read()
    if ip == '':
        continue

    ip = toInt(ip)

    flag = 0
    if len(sys.argv) > 1:
        argmask = sys.argv[1]
        argmask = toInt(argmask)
        flag = 1

        binmask = bin(argmask)
        change = 0
        for i in binmask[2:]:
            if (i == '0') and (change == 0):
                change = 1
            if (i == '1') and (change == 1):
                print('Error with network submask')
                exit(0)

    print('============================= ' + eth + ' =============================')

    if flag == 1:
        mask = argmask
    else:
        mask = os.popen('ifconfig ' + eth).read()
        res = mask.split()
        mask = res[res.index('netmask') + 1:][0]
        mask = int(mask, 16)

    #mask = (pow(2, bmask) - 1) << (32 - bmask)
    lan = ip & mask
    broadcast = ip | (mask ^ 0xFFFFFFFF)

    print('My IP:     ', toStr(ip))
    print('Netmask:   ', toStr(mask))
    print('Network:   ', toStr(lan))
    print('Broadcast: ', toStr(broadcast))
    print()
    hosts = []
    fromi = toStr(lan + 1).split('.')
    toi = toStr(broadcast - 1).split('.')
    for a in range(int(fromi[0]), int(toi[0]) + 1, 1):
        for b in range(int(fromi[1]), int(toi[1]) + 1, 1):
            for c in range(int(fromi[2]), int(toi[2]) + 1, 1):
                for d in range(int(fromi[3]), int(toi[3]) + 1, 1):
                    os.popen('ping -S ' + toStr(ip) + ' -c 1 -W 1 ' + str(a) + '.' + str(b) + '.' + str(c) + '.' + str(d))

    b = os.popen('arp -a').read()
    hosts = b.splitlines()

    print('       IP         |          MAC         |         NAME        ')
    print('---------------------------------------------------------------')
    for host in hosts:
        host = re.split("( )+", host)
        if (host[6] == '(incomplete)') or (host[10] != eth):
            continue

        lip = re.sub('[()]', '', host[2])

        if (lip == toStr(ip)) and (host[0] == '?'):
            host[0] = os.popen('scutil --get LocalHostName').read().rstrip()

        print('%16s' % lip, ' | ', '%18s' % host[6],  ' | ', host[0])