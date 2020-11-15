package org.javaweb.server;

import java.io.IOException;
import java.net.InetSocketAddress;
import java.nio.ByteBuffer;
import java.nio.channels.SelectionKey;
import java.nio.channels.Selector;
import java.nio.channels.ServerSocketChannel;
import java.nio.channels.SocketChannel;
import java.util.Iterator;
import java.util.logging.Logger;

import org.javaweb.server.common.Constants;

public class Server {
	
	private static Logger logger = Logger.getLogger("info");
	
	private volatile boolean run = true;
	
	public void startX(){
		try{
			ServerSocketChannel ssc=ServerSocketChannel.open();
			ServerInfo s = new ServerInfo();
			ssc.socket().bind(new InetSocketAddress(s.getPort()));
			ssc.configureBlocking(false);
			Selector selector=Selector.open();
			ssc.register(selector, SelectionKey.OP_ACCEPT);
			logger.info(Constants.SYS_CONFIG_VERSION+" 启动成功,监听端口["+s.getPort()+"]");
			while(run){
				try {
					if(selector.select()==0){continue;}
					Iterator<SelectionKey> it=selector.selectedKeys().iterator();
					while(it.hasNext()){
						SelectionKey sk=(SelectionKey)it.next();
						if(sk.isAcceptable()){
							ServerSocketChannel sscv=(ServerSocketChannel)sk.channel();
							SocketChannel client=sscv.accept();
//							logger.info("Client-ip:"+ client.socket().getLocalAddress()+ "client-port:"+ client.socket().getRemoteSocketAddress());
							ByteBuffer buffer=ByteBuffer.allocate(s.getMaxSize());
							int a = client.read(buffer);
							StringBuilder sb = new StringBuilder();
							while(a!=-1){
								buffer.flip();
								while(buffer.hasRemaining()){
									sb.append((char)buffer.get());
//									System.out.print(","+buffer.get());
								}
//								System.out.println(sb.toString());
								buffer.clear();
								if(a==s.getMaxSize()){
									a = client.read(buffer);
								}else{
									a = -1;
								}
							}
							Request req = new Request().parserRequest(sb.toString());//解析请求
							if("/api.jsp".equals(req.getRequestURI())&&"stop".equals(req.getParameter("action"))){
								run = false ;
							}
							ByteBuffer bb = ByteBuffer.wrap(new Response().response(req).getBytes());//内容到浏览器
							bb.rewind();
							client.write(bb);
							client.close();
						}
						it.remove();
					}
				} catch (Exception e) {
					e.printStackTrace();
//					logger.info(e.toString());
				}
			}
		} catch (IOException e) {
			e.printStackTrace();
//			logger.info(e.toString());
		}
	}
	
	public static void main(String[] args){
		new Server().startX();
	}
	
}
