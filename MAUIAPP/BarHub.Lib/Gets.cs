

namespace BarHub.Lib
{
    public class Gets
    {
        private HttpClient _httpClient;
        private string _token;
        public Gets(HttpClient httpClient,string token)
        {
            _httpClient = httpClient;
            _token = token;
        }
    }
}
