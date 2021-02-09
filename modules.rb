require 'curb'
require 'nokogiri'
require 'json'

class Timetable
  def initialize
    super

    @curl = Curl::Easy.new
    @curl.enable_cookies = true
    @curl.url = 'https://www.timetable.ul.ie/UA/Default.aspx'
    raise 'Failed init' unless @curl.perform
  end

  def fetch_courses
    results = fetch(:course)
    results[:results].map! do |c|
      {
        code: c[:code].scan(/LM\d+/).first,
        full_name: c[:code],
        name: c[:name].sub(/ \(#{c[:code]}\)\z/, '')
      }
    end
    results
  end

  def fetch_modules
    results = fetch(:module)
    results[:results] = results[:results].map do |m|
      [
        m[:code],
        m[:name].sub(/\A#{m[:code]} - /, '')
                .downcase
                .gsub(/\b('?[a-z])/, &:capitalize)
      ]
    end.to_h
    results
  end

  private

  def fetch(type)
    raise 'type must be :course or :module' unless %i[course module].include?(type)

    puts "Downloading #{type} data..."

    @curl.url = "https://www.timetable.ul.ie/UA/#{type.capitalize}Timetable.aspx"
    raise "Failed to GET #{@curl.url}" unless @curl.perform

    doc = Nokogiri::HTML(@curl.body_str)

    viewstate = doc.xpath('//input[@name="__VIEWSTATE"]').attr('value').value
    eventvalidation = doc.xpath('//input[@name="__EVENTVALIDATION"]').attr('value').value

    nodes = doc.xpath('//select[contains(@class, "DropDownSearch")][last()]/option[not(@value=-1)]')
    puts "Found #{nodes.count} nodes."
    results = nodes.map do |node|
      {
        code: node.attr('value'),
        name: node.text
      }
    end

    {
      results: results,
      viewstate: viewstate,
      eventvalidation: eventvalidation,
    }
  end
end

def save(data, filename)
  tmp_filename = "#{filename}.tmp"
  File.open(tmp_filename, 'w+') { |f| f.write(data) }
  File.rename(tmp_filename, filename)
end

timetable = Timetable.new

{
  modules: timetable.fetch_modules,
  courses: timetable.fetch_courses
}.each do |type, results|
  json_filename = "public/data/#{type}.json"
  puts "Writing #{results[:results].count} entries to #{json_filename}..."
  save(results[:results].to_json, json_filename)

  viewstate_filename = "public/data/#{type}.viewstate"
  puts "Writing #{results[:viewstate].length} bytes to #{viewstate_filename}..."
  save(results[:viewstate], viewstate_filename)

  eventvalidation_filename = "public/data/#{type}.eventvalidation"
  puts "Writing #{results[:eventvalidation].length} bytes to #{eventvalidation_filename}..."
  save(results[:eventvalidation], eventvalidation_filename)

  puts "#{type} data download complete!"
end
