require 'curb'
require 'nokogiri'
require 'json'

class Timetable
  def initialize
    super

    @curl = Curl::Easy.new
    @curl.enable_cookies = true
    @curl.url = 'https://www.timetable.ul.ie/UA/Default.aspx'
    raise "Failed init" unless @curl.perform
  end

  def fetch_courses
    fetch(:course).map do |c|
      {
        code: c[:code],
        name: c[:name].sub(/ \(#{c[:code]}\)\z/, '')
      }
    end
  end

  def fetch_modules
    fetch(:module).map do |m|
      [
        m[:code],
        m[:name].sub(/\A#{m[:code]} - /, '')
                .downcase
                .gsub(/\b('?[a-z])/, &:capitalize)
      ]
    end.to_h
  end

  private
  def fetch(type)
    raise 'type must be :course or :module' unless %i[course module].include?(type)

    puts "Downloading #{type} data..."

    @curl.url = "https://www.timetable.ul.ie/UA/#{type.capitalize}Timetable.aspx"
    raise "Failed to GET #{@curl.url}" unless @curl.perform

    doc = Nokogiri::HTML(@curl.body_str)
    nodes = doc.xpath('//select[contains(@class, "DropDownSearch")][last()]/option[not(@value=-1)]')
    puts "Found #{nodes.count} nodes."
    nodes.map do |node|
      {
        code: node.attr('value'),
        name: node.text
      }
    end
  end
end

timetable = Timetable.new

{
  modules: timetable.fetch_modules,
  courses: timetable.fetch_courses
}.each do |type, data|
  filename = "public/data/#{type}.json"
  tmp_filename = "#{filename}.tmp"

  json = data.to_json

  puts "Writing #{data.count} entries (#{json.length} bytes) to #{filename}..."

  File.open(tmp_filename, 'w+') { |f| f.write(json) }
  File.rename(tmp_filename, filename)

  puts "#{type} data download complete!"
end
