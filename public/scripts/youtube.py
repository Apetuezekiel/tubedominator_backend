import requests
from bs4 import BeautifulSoup

def search_youtube(title):
    try:
        # Replace the search query with the title you want to search for
        search_query = title
        search_url = f"https://www.youtube.com/results?search_query={search_query}"

        # Send an HTTP GET request to the search URL
        response = requests.get(search_url)

        # Check if the response status code indicates success (200 OK)
        if response.status_code == 200:
            # Parse the HTML content of the search results page
            soup = BeautifulSoup(response.text, "html.parser")

            # Find the first video link from the search results
            video_link = soup.find("a", class_="yt-simple-endpoint style-scope ytd-video-renderer")

            # Check if a video link was found
            if video_link:
                # Extract the video URL
                video_url = f"https://www.youtube.com{video_link['href']}"
                return video_url
            else:
                return "No video link found in search results"
        else:
            return f"Failed to fetch search results. Status code: {response.status_code}"

    except Exception as e:
        return str(e)

if __name__ == "__main__":
    title_to_search = input("Enter the title to search on YouTube: ")
    video_url = search_youtube(title_to_search)
    print("Video URL:", video_url)
