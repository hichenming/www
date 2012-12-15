import mongotest.*;

public class JavaServer {

    public static void main(String[] args){
        MongoTestHandler handler = new MongoTestHandler();
        MongoTest.Processor processor = new MongoTest.Processor(handler);
        ThriftServer server = new ThriftServer(processor);
        server.startServer();

    }

}
