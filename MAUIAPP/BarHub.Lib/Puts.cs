using BarHub.Models;
using BarHub.Models.Enums;
using System.Diagnostics;
using Section = BarHub.Models.Section;

namespace BarHub.Lib
{
    public class Puts
    {
        private Methods _methods;
        public Puts(Methods methods)
        {
            _methods = methods;
        }

        

        public async Task ModifyRestaurant(Restaurant restaurant)
        {
            try
            {
                var data = new
                {
                    owner_name = restaurant.Name,
                    owner_CIF = restaurant.Cif,
                    owner_contact_email = restaurant.Email,
                    owner_contact_phone = restaurant.Phone,
                    owner_logo = restaurant.Logo,
                };

                var user = await _methods.Put<object, User>($"{ApiConstants.UPDATE_RESTAURANT}/{restaurant.Id}", data);
            }
            catch (Exception e)
            {
                Trace.WriteLine(e.Message);
            }

        }

        public async Task ModifySection(Section section)
        {
            try
            {
                var data = new
                {
                    section_name = section.Name,
                    section_img = section.Image,
                };

                await _methods.Put<object, Section>($"{ApiConstants.UPDATE_SECTION}/{section.Id}", data);
            }
            catch (Exception e)
            {
                Trace.WriteLine(e.Message);
            }
        }

        public async Task ModifyArticle(Article article)
        {
            try
            {
                var data = new
                {
                    article_name = article.Name,
                    article_price = article.Price,
                    article_img = article.Image
                };

                await _methods.Put<object, Article>($"{ApiConstants.UPDATE_ARTICLE}/{article.Id}", data);
            }
            catch (Exception e)
            {
                Trace.WriteLine(e.Message);
            }
        }


        public async Task ModifyWorker(User worker)
        {
            try
            {
                var data = new
                {
                    worker_name = worker.Name,
                    worker_username = worker.Username,
                    worker_password = worker.Password,
                    worker_roles = worker.Roles.Select(role => (int)role).ToArray()
                };

                await _methods.Put<object, User>($"{ApiConstants.UPDATE_WORKER}/{worker.Id}", data);
            }
            catch (Exception e)
            {
                Trace.WriteLine(e.Message);
            }
        }

        public async Task ModifyOrderLine(OrderLine orderLine, OrderState state)
        {
            try
            {
                var data = new
                {
                    id_state = state,
                };

                await _methods.Put<object, User>($"/set_state_orderLine/{orderLine.Id}", data);
            }
            catch (Exception e)
            {
                Trace.WriteLine(e.Message);
            }
        }
    }
}
