<template>
    <div class="col-12 col-md-3 col-xl-2 bd-sidebar">
        <div class="filters p-2">
          <gmap-autocomplete style="width: 100%;" placeholder="Enter your location" 
            @place_changed="setPlace" :component-restrictions="{country:['PH']}">
          </gmap-autocomplete>
          
          <form class="form-inline">
               <label for="status" class="col-xs-4 control-label">Status&nbsp; </label>
             <select id="status" class="custom-select custom-select-sm"  v-model="status" @change="changeStatus">
              <option value="0">Unvalidated</option>
              <option value="1">Unresolved</option>
            </select>
          </form>
          <form class="form-inline" style="width:100%">
            <label for="radius" class="col-xs-4 control-label">Radius</label>
            <select id="radius" class="custom-select custom-select-sm" v-model="radius" @change="changeRadius" >
              <option value="10">10 km </option>
              <option value="20">20 km </option>
              <option value="30">30 km </option>
              <option value="40">40 km </option>
            </select>
          </form>
          <form class="form-inline">
            <label for="sortby" class="col-xs-4 control-label">Sort by</label>
            <select id= "sortby" class="custom-select custom-select-sm" v-model="sortby" @change="changeSort">
              <option value="distance">Distance</option>
              <option value="unform_date">Report Date</option>
            </select>
            <button type="button" class="btn btn-sm js-add-btn" v-if="sortOrder==='desc'" @click="sortOrder='asc'">
               <i class="fas fa-sort-amount-up"></i>
            </button>
            <button type="button" class="btn btn-sm js-add-btn" v-else>
              <i class="fas fa-sort-amount-down" @click="sortOrder='desc'"></i>
            </button>
          </form>
        </div>
        <div class="reports-list p-2">
            <div v-for="(item,i) in sortedResults" :key="i"
                class="reports-item"
                @click="focusMarker(item)"
                >
                {{item.text}}
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props:{userLat:0,userLng:0},
        data: function() {
            return {
                results:[],
                currentPlace:{lat:this.userLat, lng:this.userLng},
                radius:20,
                status:0,
                sortby:'unform_date',
                sortOrder:'desc'
            }
        },


          /*
          *Unvalidated: all reports with is_validated = 0
          *Unresolved: reports of responder with is_validated=1
          */

        methods: {
            setPlace(place) {
                let center = {
                  lat: place.geometry.location.lat(),
                  lng: place.geometry.location.lng()
                };
                this.currentPlace = center;
                Bus.$emit('place_filter', this.currentPlace);
            },

            respondToReport(index){
                axios.post('/api/respondReport', {report_id:this.results[index].report_id}).then(response=> {
                      let data = response.data.report;
                      console.log("report_id",data);
              });
            },

            changeSort: function(order){
                this.sortOrder=order;
            },

            focusMarker(item){
                Bus.$emit('marker_result_clicked',item);
            },

            changeRadius(){
                Bus.$emit('changed_radius', this.radius);
                console.log("changed radius");
            },

            changeStatus(){
                Bus.$emit('changed_status', this.status);
            },
        },

        created(){
            Bus.$on('reports_fetched',data=>{
                this.results=data.results;
                console.log('event data',data);
            })

            Bus.$on('notifRead', report_id=> {
                console.log("sortedResults",this.sortedResults);
               for (var i=0; i < this.sortedResults.length; i++) {
                  if (this.sortedResults[i].report_id === report_id) {
                      this.focusMarker(this.sortedResults[i])
                      console.log("notifread",this.sortedResults[i]);
                      debugger;
                  }
                }
    
              }
            )
        },

        computed:{
            sortedResults: function(){
                return this._.orderBy(this.results, this.sortby,this.sortOrder)               
            }
        }
    }
</script>
