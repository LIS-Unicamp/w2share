require 'taverna-t2flow'
foo = File.new("/home/lucas/Dropbox/Doutorado/Fapesp/Presentations/workflows/setup-claudia-presentation.t2flow", "r")
bar = T2Flow::Parser.new.parse(foo)
output_file = File.new("/home/lucas/Dropbox/Doutorado/Fapesp/Presentations/workflows/setup-claudia-presentation.png", "w+")
T2Flow::Dot.new.write_dot(output_file, bar)
`dot -Tpng -o "/home/lucas/Dropbox/Doutorado/Fapesp/Presentations/workflows/setup-claudia-presentation.png" #{output_file.path}`