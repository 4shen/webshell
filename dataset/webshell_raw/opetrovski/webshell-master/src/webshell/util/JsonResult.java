package webshell.util;

import java.io.ByteArrayOutputStream;
import java.io.IOException;

import com.fasterxml.jackson.core.JsonEncoding;
import com.fasterxml.jackson.core.JsonFactory;
import com.fasterxml.jackson.core.JsonGenerator;

/**
 * Returns the subscriptions of a user.
 */
public class JsonResult {
    private ByteArrayOutputStream out;
    private JsonGenerator g;

    /**
     * Constructor
     */
    public JsonResult() {
        try {
            out = new ByteArrayOutputStream();
            g = new JsonFactory().createJsonGenerator(out, JsonEncoding.UTF8);
            g.enable(JsonGenerator.Feature.ESCAPE_NON_ASCII);
        } catch (IOException ioe) {
            // Ignore
        }
    }

    public void begin() {
        try {
            g.writeStartObject();
        } catch (IOException ioe) {
            // Ignore
        }
    }

    public void end() {
        try {
            g.writeEndObject();
        } catch (IOException ioe) {
            // Ignore
        }
    }

    public void add(String key, String value) {
        try {
            g.writeStringField(key, value);
        } catch (IOException ioe) {
            // Ignore
        }
    }

    public void add(String key, boolean value) {
        try {
            g.writeBooleanField(key, value);
        } catch (IOException ioe) {
            // Ignore
        }
    }
    
    public void beginArray() {
        try {
            g.writeStartArray();
        } catch (IOException ioe) {
            // Ignore
        }
    }

    public void beginArray(String attributename) {
        try {
            g.writeArrayFieldStart(attributename);
        } catch (IOException ioe) {
            // Ignore
        }
    }

    public void addToArray(String value) {
        try {
            g.writeString(value);
        } catch (IOException ioe) {
            // Ignore
        }
    }

    public void endArray() {
        try {
            g.writeEndArray();
        } catch (IOException ioe) {
            // Ignore
        }
    }

    public String getJson() {
        try {
            g.close();
            return out.toString();
        } catch (IOException ioe) {
            return ioe.toString();
        }
    }
}
