using BarHub.Models;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace BarHub.Lib
{
    public class Deletes
    {
        private Methods _methods;
        public Deletes(Methods methods)
        {
            _methods = methods;
        }

        public async Task DeleteRestaurant(int id)
        {
            try
            {
                await _methods.Delete<Restaurant>($"{ApiConstants.DELETE_RESTAURANT}/{id}");
            }
            catch (Exception e)
            {
                Trace.WriteLine(e.Message);
            }
        }

        public async Task DeleteSection(int id)
        {
            try
            {
                await _methods.Delete<Section>($"{ApiConstants.DELETE_SECTION}/{id}");
            }
            catch (Exception e)
            {
                Trace.WriteLine(e.Message);
            }
        }
    }
}
