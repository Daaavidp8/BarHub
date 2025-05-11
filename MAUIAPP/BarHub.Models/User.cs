using BarHub.Models.Enums;
using Newtonsoft.Json;
using System.Text.Json.Serialization;

namespace BarHub.Models
{
    public class User
    {
        [JsonProperty("id_user")]
        public int Id { get; set; }

        [JsonProperty("name")]
        public string Name { get; set; }

        [JsonProperty("username")]
        public string Username { get; set; }

        public string Password { get; set; }
        [JsonProperty("restaurant")]
        public int? Restaurant { get; set; }
        [JsonProperty("token")]
        public string Token { get; set; }

        [JsonProperty("roles")]
        public List<Roles> Roles { get; set; }

    }
}
