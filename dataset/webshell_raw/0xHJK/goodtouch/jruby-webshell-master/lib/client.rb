class Browser

  def initialize
    # A Shell is a window in SWT parlance.
    #@shell = Swt::Widgets::Shell.new(Swt::SWT::NO_TRIM | Swt::SWT::ON_TOP)
    @shell = Swt::Widgets::Shell.new
  
    # Set the window title bar text
    @shell.text = "Browser Example"
  
    # A Shell must have a layout. FillLayout is the simplest.
    layout = Swt::Layout::FillLayout.new
    @shell.setLayout(layout)
    # A Display is the connection between SWT and the native GUI.
    display = Swt::Widgets::Display.get_current
    # @shell.setBounds(display.getPrimaryMonitor().getBounds())

    # Create a button widget
    browser = Swt::Browser.new(@shell, Swt::SWT::PUSH)
    # file_path = File.expand_path(File.join(File.dirname(__FILE__),"../public/map.html"))
    browser.setUrl('http://localhost:4567')
  
    # And this displays the Shell
    @shell.open
  end
  
  # This is the main gui event loop
  def start
    display = Swt::Widgets::Display.get_current
  
    Thread.new do
      require 'server'
    end
  
    # until the window (the Shell) has been closed
    while !@shell.isDisposed
  
      # check for and dispatch new gui events
      display.sleep unless display.read_and_dispatch
    end

    display.dispose
  end
end

Swt::Widgets::Display.set_app_name "Browser Example"
