#!/usr/bin/python3

import socket, sys
from threading import Thread


class UDPClient(Thread):

    UDP_IP = "192.168.100.4"
    UDP_PORT_SERVER = 55554
    UDP_PORT_CLIENT = 55555

    BUFFER_SIZE = 512

    def __init__(self):
        super().__init__()
        try:
            self.udpSocket = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
            self.udpSocket.bind(('', UDPClient.UDP_PORT_CLIENT))
        except socket.error as e:
            print("Ошибка создания сокета: {}".format(e))
            exit(1)
        self.daemon = True

    def run(self):
        client.sendRequest('--init', (self.UDP_IP, self.UDP_PORT_SERVER))
        while True:
            data, address = self.udpSocket.recvfrom(self.BUFFER_SIZE)
            print(data.decode())

    def sendRequest(self, data, client):
        try:
            self.udpSocket.sendto(data.encode(), client)
        except socket.error as e:
            print("Ошибка при отправке запроса: ".format(e))
            exit(1)


if __name__ == "__main__":

    if len(sys.argv) > 1:
        UDPClient.UDP_IP = sys.argv[1]
        if len(sys.argv) > 2:
            UDPClient.UDP_PORT_SERVER = int(sys.argv[2])
            if len(sys.argv) > 3:
                UDPClient.UDP_PORT_CLIENT = int(sys.argv[3])

    client = UDPClient()
    client.start()

    userInput = ''
    while userInput != '--exit':
        userInput = input()
        client.sendRequest(str(userInput), (UDPClient.UDP_IP, UDPClient.UDP_PORT_SERVER))
