module WebShell
  def self.load_prerequisites(options={})
    require 'java'
  
    $:.push(File.expand_path("../../vendor/swt/lib", __FILE__))
    $:.push(File.expand_path("../../vendor/tilt/lib", __FILE__))
    $:.push(File.expand_path("../../vendor/rack/lib", __FILE__))
    $:.push(File.expand_path("../../vendor/rack-protection/lib", __FILE__))
    $:.push(File.expand_path("../../vendor/sinatra/lib", __FILE__))
  
    require 'swt'  
  end
end
