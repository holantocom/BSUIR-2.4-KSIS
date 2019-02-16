#!/usr/bin/python3

import time, sys, os, socket
from threading import Thread


class UDPServer(Thread):
    UDP_IP = "192.168.100.4"
    UDP_PORT_SERVER = 55554
    UDP_PORT_CLIENT = 55555

    BUFFER_SIZE = 512

    CLIENTS = []

    def __init__(self):
        Thread.__init__(self)
        self.udpSocket = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        try:
            self.udpSocket.bind((UDPServer.UDP_IP, UDPServer.UDP_PORT_SERVER))
        except socket.error as e:
            print("Ошибка создания сокета: {}".format(e))
            exit(1)

    def run(self):
        print("{}: сервер запущен c IP: ".format(time.ctime()) + self.UDP_IP)
        while True:
            data, address = self.udpSocket.recvfrom(UDPServer.BUFFER_SIZE)
            if address[0] not in self.CLIENTS:
                if data.decode() == '--init':
                    self.CLIENTS.append(address[0])
                    self.sendRequest('Пользователей в сети: ' + str(len(self.CLIENTS)), (address[0], self.UDP_PORT_CLIENT))
                    print('[NEW] ' + address[0])
                    self.SendMessages(self.CLIENTS, 'К сети присоединился новый пользователь', address[0])
                continue

            if data.decode() == '--exit':
                self.CLIENTS.remove(address[0])
                self.SendMessages(self.CLIENTS, 'Из сети вышел пользователь ' + address[0], address[0])
                print('[EXIT] ' + address[0])
                continue

            data = address[0] + ': ' + data.decode()
            self.SendMessages(self.CLIENTS, data, address[0])
            print(self.getCurrentTime() + ' ' + data)

    def getCurrentTime(self):
        return time.strftime("%H:%M:%S", time.localtime())

    def SendMessages(self, clients, data, myip):
        for ip in clients:
            if ip != myip:
                self.sendRequest(data, (ip, self.UDP_PORT_CLIENT))

    def sendRequest(self, data, client):
        try:
            self.udpSocket.sendto(data.encode(), client)
        except socket.error:
            print("Ошибка при отправке запроса!")
            exit(1)


if __name__ == "__main__":

    if len(sys.argv) > 1:
        UDPServer.UDP_PORT_SERVER = int(sys.argv[1])
        if len(sys.argv) > 2:
            UDPServer.UDP_PORT_CLIENT = int(sys.argv[2])

    UDPServer.UDP_IP = socket.gethostbyname(socket.gethostname())

    server = UDPServer()
    server.start()
