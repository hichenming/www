#!/usr/bin/env python
#encoding=utf-8

import sys
sys.path.append('./gen-py')
import pymongo
import json
from mongotest import MongoTest

class MongoTestHandler(MongoTest.Iface):

    def __init__(self):
        #self.con = pymongo.Connection()
        pass

    def getServerStatus(self, ):
        #data = self.con.admin.command(pymongo.son_manipulator.SON([('serverStatus', 1)]))
        #return json.dumps(data['connections'])
        return 'test'

