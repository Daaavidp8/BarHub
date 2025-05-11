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


        public async Task DeleteArticle(int id)
        {
            try
            {
                await _methods.Delete<Article>($"{ApiConstants.DELETE_ARTICLE}/{id}");
            }
            catch (Exception e)
            {
                Trace.WriteLine(e.Message);
            }
        }

        public async Task DeleteWorker(int id)
        {
            try
            {
                await _methods.Delete<User>($"{ApiConstants.DELETE_WORKER}/{id}");
            }
            catch (Exception e)
            {
                Trace.WriteLine(e.Message);
            }
        }

        public async void DeleteTable(int restaurant)
        {
            try
            {
                _methods.Delete<Table>($"{ApiConstants.DELETE_TABLE}/{restaurant}");
            }
            catch (Exception e)
            {
                Trace.WriteLine(e.Message);
            }
        }
    }
}
