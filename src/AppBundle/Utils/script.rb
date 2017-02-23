require 'taverna-t2flow'

file_workflow_path = ARGV[0]
file_image_path = ARGV[1]

file_workflow = File.new(file_workflow_path, "r")
workflow_object = T2Flow::Parser.new.parse(file_workflow)

file_image = File.new(file_image_path, "w+")
T2Flow::Dot.new.write_dot(file_image, workflow_object)
`/usr/bin/dot -Tsvg -o #{file_image_path} #{file_image.path}`