package webshell.filesystem.tail;

import java.util.ArrayList;
import java.util.List;

import org.apache.commons.io.input.Tailer;
import org.apache.commons.io.input.TailerListener;

public class TailListener implements TailerListener {

    private final List<String> lines = new ArrayList<String>();

    volatile Exception exception = null;
    
    volatile int notFound = 0;

    volatile int rotated = 0;
    
    volatile int initialised = 0;

    public void handle(String line) {
        lines.add(line);
    }
    public List<String> getLines() {
        return lines;
    }
    public void clear() {
        lines.clear();
    }
    public void handle(Exception e) {
        exception = e;
    }
    public void init(Tailer tailer) {
        initialised++; // not atomic, but OK because only updated here.
    }
    public void fileNotFound() {
        notFound++; // not atomic, but OK because only updated here.
    }
    public void fileRotated() {
        rotated++; // not atomic, but OK because only updated here.
    }
}