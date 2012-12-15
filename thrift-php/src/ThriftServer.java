
import org.apache.thrift.server.TServer;
import org.apache.thrift.server.TServer.Args;
import org.apache.thrift.server.TSimpleServer;
import org.apache.thrift.server.TThreadedSelectorServer;
import org.apache.thrift.server.TThreadPoolServer;
import org.apache.thrift.transport.TSSLTransportFactory;
import org.apache.thrift.transport.TServerSocket;
import org.apache.thrift.transport.TNonblockingServerSocket;
import org.apache.thrift.transport.TServerTransport;
import org.apache.thrift.transport.TFramedTransport;
import org.apache.thrift.transport.TNonblockingServerTransport;
import org.apache.thrift.transport.TTransportFactory;
import org.apache.thrift.transport.TSSLTransportFactory.TSSLTransportParameters;
import org.apache.thrift.protocol.TProtocolFactory;
import org.apache.thrift.protocol.TCompactProtocol;


public class ThriftServer {
  public org.apache.thrift.TProcessor processor;

  public ThriftServer(org.apache.thrift.TProcessor processor){
    this.processor = processor;
  }

  public void startServer() {
    try {
      Runnable threadPool = new Runnable() {
        public void run() {
          threadPool(processor);
        }
      };
      //Runnable secure = new Runnable() {
        //public void run() {
          //secure(processor);
        //}
      //};

      new Thread(threadPool).start();
      //new Thread(secure).start();
    } catch (Exception x) {
      x.printStackTrace();
    }
  }

  public void threadPool(org.apache.thrift.TProcessor processor) {
    try {
      TServerTransport serverTransport = new TServerSocket(9090);
      //TServer server = new TSimpleServer(new Args(serverTransport).processor(processor));

      // Use this for a multithreaded server
      TThreadPoolServer.Args args = new TThreadPoolServer.Args(serverTransport).processor(processor);
      args.maxWorkerThreads = 50;
      TServer server = new TThreadPoolServer(args);

      System.out.println("Starting the server...");
      server.serve();
    } catch (Exception e) {
      e.printStackTrace();
    }
  }

  public void threadSelector(org.apache.thrift.TProcessor processor) {
    try {
      TNonblockingServerTransport serverTransport = new TNonblockingServerSocket(9090);
      //异步IO，需要使用TFramedTransport，它将分块缓存读取。
      TTransportFactory transportFactory = new TFramedTransport.Factory();
      //使用高密度二进制协议
      TProtocolFactory proFactory = new TCompactProtocol.Factory();
      TServer server = new TThreadedSelectorServer(
              new TThreadedSelectorServer.Args(serverTransport)
              .protocolFactory(proFactory)
              .transportFactory(transportFactory)
              .processor(processor)
              );

      System.out.println("Starting the server...");
      server.serve();
    } catch (Exception e) {
      e.printStackTrace();
    }
  }

  public void secure(org.apache.thrift.TProcessor processor) {
    try {
      /*
       * Use TSSLTransportParameters to setup the required SSL parameters. In this example
       * we are setting the keystore and the keystore password. Other things like algorithms,
       * cipher suites, client auth etc can be set.
       */
      TSSLTransportParameters params = new TSSLTransportParameters();
      // The Keystore contains the private key
      params.setKeyStore("./lib/java/test/.keystore", "thrift", null, null);

      /*
       * Use any of the TSSLTransportFactory to get a server transport with the appropriate
       * SSL configuration. You can use the default settings if properties are set in the command line.
       * Ex: -Djavax.net.ssl.keyStore=.keystore and -Djavax.net.ssl.keyStorePassword=thrift
       *
       * Note: You need not explicitly call open(). The underlying server socket is bound on return
       * from the factory class.
       */
      TServerTransport serverTransport = TSSLTransportFactory.getServerSocket(9093, 0, null, params);
      //TServer server = new TSimpleServer(new Args(serverTransport).processor(processor));

      // Use this for a multi threaded server
      TThreadPoolServer.Args args = new TThreadPoolServer.Args(serverTransport).processor(processor);
      args.maxWorkerThreads = 50;
       TServer server = new TThreadPoolServer(args);

      System.out.println("Starting the secure server...");
      server.serve();
    } catch (Exception e) {
      e.printStackTrace();
    }
  }
}
