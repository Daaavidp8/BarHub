using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.Models
{
    public class Section
    {
        public int Id { get; set; }
        public string Name { get; set; }
        public string Image { get; set; }
        public List<Article> Articles { get; set; }
    }
}
