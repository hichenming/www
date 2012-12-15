#!/usr/bin/env python
#encoding=utf-8

import sys
sys.path.append('./gen-py')
from ThriftServer import ThriftServer
from MongoTestHandler import MongoTestHandler
from mongotest import MongoTest

handler = MongoTestHandler()
processor = MongoTest.Processor(handler)
server = ThriftServer(processor, 9090)

server.startServer()
