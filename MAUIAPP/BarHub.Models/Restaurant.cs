using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.Models
{
    public class Restaurant
    {
        public Guid Id { get; set; }
        public string Name { get; set; }
        public string Cif { get; set; }
        public string Address { get; set; }
        public string Phone { get; set; }

        public List<Section> Sections { get; set; }
        public List<User> Users { get; set; }
    }
}
