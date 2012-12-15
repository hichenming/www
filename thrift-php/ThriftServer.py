#!/usr/bin/env python
#encoding=utf-8

import sys
sys.path.append('./gen-py')

from thrift.transport import TSocket
from thrift.transport import TTransport
from thrift.protocol import TBinaryProtocol
from thrift.server import TServer

class ThriftServer:

    def __init__(self, processor, port=9090):
        self.processor = processor
        self.port = port

    def startServer(self):
        processor = self.processor
        transport = TSocket.TServerSocket(port=self.port)
        tfactory = TTransport.TBufferedTransportFactory()
        pfactory = TBinaryProtocol.TBinaryProtocolFactory()

        #server = TServer.TSimpleServer(processor, transport, tfactory, pfactory)

        # You could do one of these for a multithreaded server
        #server = TServer.TThreadedServer(processor, transport, tfactory, pfactory)
        #server = TServer.TThreadPoolServer(processor, transport, tfactory, pfactory)
        #server.daemon = True #enable ctrl+c to exit the server
        #server.setNumThreads(100);
        server = TServer.TForkingServer(processor, transport, tfactory, pfactory)

        print 'Starting the server...'
        server.serve()
        print 'done.'
