using BarHub.Models.Enums;
using System.Text.Json.Serialization;

namespace BarHub.Models
{
    public class User
    {
        public int Id { get; set; }
        public string Name { get; set; }
        public string Username { get; set; }

        [JsonIgnore]
        public string Password { get; set; }

        public int? Restaurant { get; set; }

        public string Token { get; set; }

        public List<Roles> Roles { get; set; }

    }
}
