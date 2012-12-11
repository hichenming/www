#!/usr/bin/env python
#encoding=utf-8

import sys
sys.path.append('./gen-py')

from thrift.transport import TSocket
from thrift.transport import TTransport
from thrift.protocol import TBinaryProtocol
from thrift.server import TServer

class ThriftServer:

    def __init__(self, serviceClassHander, serverClass, port=9090):
        self.handler = serviceClassHander
        self.port = port
        self.serverClass = serverClass

    def startServer(self):
        processor = self.serverClass.Processor(self.handler)
        transport = TSocket.TServerSocket(port=self.port)
        tfactory = TTransport.TBufferedTransportFactory()
        pfactory = TBinaryProtocol.TBinaryProtocolFactory()

        #server = TServer.TSimpleServer(processor, transport, tfactory, pfactory)

        # You could do one of these for a multithreaded server
        #server = TServer.TThreadedServer(processor, transport, tfactory, pfactory)
        server = TServer.TThreadPoolServer(processor, transport, tfactory, pfactory)
        server.setNumThreads(100);

        print 'Starting the server...'
        server.serve()
        print 'done.'
