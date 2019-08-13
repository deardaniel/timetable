require 'open-uri'
require 'nokogiri'
require 'json'

def get(type)
  raise 'type must be :course or :module' unless %i[course module].include?(type)

  puts "Downloading #{type} data..."

  url = "https://www.timetable.ul.ie/UA/#{type.capitalize}Timetable.aspx".freeze
  # url = "#{type.capitalize}Timetable.aspx".freeze
  doc = Nokogiri::HTML(open(url))
  nodes = doc.xpath('//select[contains(@class, "DropDownSearch")][last()]/option[not(@value=-1)]')
  nodes.map do |node|
    {
      code: node.attr('value'),
      name: node.text
    }
  end
end

def get_courses
  get(:course).map do |c|
    {
      code: c[:code],
      name: c[:name].sub(/ \(#{c[:code]}\)\z/, '')
    }
  end
end

def get_modules
  get(:module).map do |m|
    [
      m[:code],
      m[:name].sub(/\A#{m[:code]} - /, '')
              .downcase
              .gsub(/\b('?[a-z])/, &:capitalize)
    ]
  end.to_h
end

{
  modules: get_modules,
  courses: get_courses
}.each do |type, data|
  filename = "public/data/#{type}.json"
  tmp_filename = "#{filename}.tmp"

  json = data.to_json

  puts "Writing #{data.count} (#{json.length} bytes) to #{filename}..."

  File.open(tmp_filename, 'w+') { |f| f.write(json) }
  File.rename(tmp_filename, filename)

  puts "#{type} data download complete!"
end
