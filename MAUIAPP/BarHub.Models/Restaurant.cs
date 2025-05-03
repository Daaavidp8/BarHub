using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Newtonsoft.Json;

namespace BarHub.Models
{
    public class Restaurant
    {

        [JsonProperty("id_restaurant")]
        public int Id { get; set; }

        [JsonProperty("name")]
        public string Name { get; set; }

        [JsonProperty("cif")]
        public string Cif { get; set; }

        [JsonProperty("contactPhone")]
        public string Phone { get; set; }

        [JsonProperty("contactEmail")]
        public string Email { get; set; }

        [JsonProperty("logo")]
        public string Logo { get; set; }

        [JsonProperty("sections")]
        public List<Section> Sections { get; set; }

        [JsonProperty("users")]
        public List<User> Users { get; set; }


        public Restaurant Clone()
        {
            return new Restaurant
            {
                Id = this.Id,
                Name = this.Name,
                Cif = this.Cif,
                Phone = this.Phone,
                Email = this.Email,
                Logo = this.Logo,
                Sections = this.Sections != null ? new List<Section>(this.Sections) : new List<Section>(),
                Users = this.Users != null ? new List<User>(this.Users) : new List<User>()
            };
        }

    }
}
