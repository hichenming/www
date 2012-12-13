
import org.apache.thrift.TException;

import com.alibaba.fastjson.JSON;
import com.mongodb.BasicDBObject;
import com.mongodb.CommandResult;
import com.mongodb.DB;
import com.mongodb.DBObject;
import com.mongodb.Mongo;

import mongotest.*;

public class MongoTestHandler implements mongotest.MongoTest.Iface{

    public String getServerStatus(){

        try{
            Mongo m = new Mongo();
            DB db = m.getDB("admin");
            DBObject cmd = new BasicDBObject();
            cmd.put("serverStatus", 1);
            CommandResult cr = db.command(cmd);
            Object b = cr.get("connections");
            String s = JSON.toJSONString(b);
            return s;
        }catch(Exception e){
            return "Exception";
        }
    }

}
