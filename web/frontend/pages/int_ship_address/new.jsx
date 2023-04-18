import {
    Card,
    Page,
    Layout,
    TextContainer,
    Image,
    Stack,
    Link,
    Heading,
    FormLayout, TextField, Button, Select
    } from "@shopify/polaris";
import { TitleBar, Toast, useAppBridge } from "@shopify/app-bridge-react";
import { Redirect } from "@shopify/app-bridge/actions";
import _ from "lodash";
import {useState, useCallback} from 'react';
import { useAppQuery, useAuthenticatedFetch } from "../../hooks";
import { useSearchParams } from "react-router-dom";

export default function HomePage() {
    const app = useAppBridge();
    const redirect = Redirect.create(app);
    const fetch = useAuthenticatedFetch();

    const emptyToastProps = { content: null };
    const [toastProps, setToastProps] = useState(emptyToastProps);
    const toastMarkup = toastProps.content && (
        <Toast {...toastProps} onDismiss={() => setToastProps(emptyToastProps)} />
    );

    const [isLoadingAddNew, setisLoadingAddNew] = useState(false);
    const [countryOptions, setCountryOptions] = useState([]);
    const [stateOptions, setStateOptions] = useState([]);

    const [isa_id, set_isa_id] = useState('');
    const [isa_first_name, set_isa_first_name] = useState('');
    const [isa_last_name, set_isa_last_name] = useState('');
    const [isa_address_1, set_isa_address_1] = useState('');
    const [isa_address_2, set_isa_address_2] = useState('');
    const [isa_city, set_isa_city] = useState('');
    const [isa_state, set_isa_state] = useState('');
    const [isa_state_code, set_isa_state_code] = useState('');
    const [isa_country, set_isa_country] = useState('');
    const [isa_country_code, set_isa_country_code] = useState('');
    const [isa_zipcode, set_isa_zipcode] = useState('');

    const [searchParams, setSearchParams] = useSearchParams();
    let editId = searchParams.get("id");
    if(editId && editId>0){
        const { data: dataGetIntShip, refetch: refetchGetIntShip, isLoading: isLoadingGetIntShip, isRefetching: isRefetchingGetIntShip } = useAppQuery({
            url: "/api/get_int_ship_address/"+editId,
            reactQueryOptions: {
                onSuccess: () => {
                    if(dataGetIntShip && dataGetIntShip.success=='true' && Object.keys(dataGetIntShip.data).length > 0){
                        set_isa_id(dataGetIntShip.data.isa_id);
                        set_isa_first_name(dataGetIntShip.data.isa_first_name);
                        set_isa_last_name(dataGetIntShip.data.isa_last_name);
                        set_isa_address_1(dataGetIntShip.data.isa_address_1);
                        set_isa_address_2(dataGetIntShip.data.isa_address_2);
                        set_isa_city(dataGetIntShip.data.isa_city);
                        set_isa_state(dataGetIntShip.data.isa_state);
                        set_isa_state_code(dataGetIntShip.data.isa_state_code);
                        set_isa_country(dataGetIntShip.data.isa_country);
                        set_isa_country_code(dataGetIntShip.data.isa_country_code);
                        set_isa_zipcode(dataGetIntShip.data.isa_zipcode);
                    }else{
                        setTimeout(function(){
                            redirect.dispatch(Redirect.Action.APP, '/int_ship_address');
                        },2000);
                    }
                }
            }
        });
    }

    const { data: dataGetShipZone, refetch: refetchGetShipZone, isLoading: isLoadingGetShipZone, isRefetching: isRefetchingGetShipZone } = useAppQuery({
        url: "/api/get_shipping_zones",
        reactQueryOptions: {
            onSuccess: () => {
                let countryOpt = [];
                if(dataGetShipZone && dataGetShipZone.success=='true' && Object.keys(dataGetShipZone.data).length > 0){
                    Object.keys(dataGetShipZone.data).forEach(function(val){
                        if(val!='*'){
                            countryOpt.push({label:dataGetShipZone.data[val].name, value: dataGetShipZone.data[val].code});
                        }
                    });
                }
                setCountryOptions(countryOpt);
            }
        }
    });


    const handleAddNew = async () => {
        if(isa_first_name==''){ setToastProps({ content: 'Please enter shipping first name', error: true });return; }
        if(isa_last_name==''){ setToastProps({ content: 'Please enter shipping last name', error: true });return; }
        if(isa_address_1==''){ setToastProps({ content: 'Please enter shipping address 1', error: true });return; }
        if(isa_city==''){ setToastProps({ content: 'Please enter shipping city', error: true });return; }
        if(isa_zipcode==''){ setToastProps({ content: 'Please enter shipping zipcode', error: true });return; }
        if(isa_country_code==''){ setToastProps({ content: 'Please select shipping country', error: true });return; }

        setisLoadingAddNew(true);
        var formData = new FormData();

        formData.append('isa_id', isa_id);
        formData.append('isa_first_name', isa_first_name);
        formData.append('isa_last_name', isa_last_name);
        formData.append('isa_address_1', isa_address_1);
        formData.append('isa_address_2', isa_address_2);
        formData.append('isa_city', isa_city);
        formData.append('isa_zipcode', isa_zipcode);
        formData.append('isa_country_code', isa_country_code);
        formData.append('isa_country', isa_country);
        formData.append('isa_state_code', isa_state_code);
        formData.append('isa_state', isa_state);

        const rawResponse = await fetch('/api/post_int_ship_address', {
            method: 'POST',
            body: formData
        });
        const obj = await rawResponse.json();
        if(obj.success == 'true'){
            setisLoadingAddNew(false);
            setToastProps({
                content: obj.message
            });
            redirect.dispatch(Redirect.Action.APP, '/int_ship_address');
        }else{
            setisLoadingAddNew(false);
            setToastProps({
                content: obj.message,
                error: true
            });
        }
    }
    const primaryFooterAction = {
        content:"Save",
        loading:isLoadingAddNew || false,
        onAction:handleAddNew
    };
    const secondaryFooterActions = [
        {
            content:"Cancel",
            disabled:isLoadingAddNew || false,
            onAction:() => {
                redirect.dispatch(Redirect.Action.APP, '/int_ship_address');
            }
        }
    ];

  return (
      <Page title="Manage International Shipping Address" primaryAction={{
            content:"Back",
            onAction:() => { redirect.dispatch(Redirect.Action.APP, '/int_ship_address'); }
        }}>
        <Layout>
          <Layout.Section>
            <Card sectioned primaryFooterAction={primaryFooterAction} secondaryFooterActions={secondaryFooterActions} >
                <FormLayout>
                    <FormLayout.Group>
                        <TextField
                        type="text"
                        label="First Name"
                        helpText=""
                        id="isa_first_name"
                        name="isa_first_name"
                        value={isa_first_name}
                        onChange={(value) => {
                            set_isa_first_name(value);
                        }}
                        />
                        <TextField
                        type="text"
                        label="Last Name"
                        helpText=""
                        id="isa_last_name"
                        name="isa_last_name"
                        value={isa_last_name}
                        onChange={(value) => {
                            set_isa_last_name(value);
                        }}
                        />
                    </FormLayout.Group>
                    <FormLayout.Group>
                        <TextField
                        type="text"
                        label="Address 1"
                        helpText=""
                        id="isa_address_1"
                        name="isa_address_1"
                        value={isa_address_1}
                        onChange={(value) => {
                            set_isa_address_1(value);
                        }}
                        />
                        <TextField
                        type="text"
                        label="Address 2"
                        helpText=""
                        id="isa_address_2"
                        name="isa_address_2"
                        value={isa_address_2}
                        onChange={(value) => {
                            set_isa_address_2(value);
                        }}
                        />
                        <TextField
                        type="text"
                        label="City"
                        helpText=""
                        id="isa_city"
                        name="isa_city"
                        value={isa_city}
                        onChange={(value) => {
                            set_isa_city(value);
                        }}
                        />
                    </FormLayout.Group>
                    <FormLayout.Group>
                        <Select
                        label="Country"
                        id="isa_country_code"
                        name="isa_country_code"
                        placeholder="Select country"
                        options={countryOptions}
                        value={isa_country_code}
                        onChange={(value) => {
                            set_isa_country_code(value);
                            _.map(countryOptions,function(c){
                                if(c.value == value){
                                    set_isa_country(c.label);
                                }
                            });

                            let stateOpt = [];
                            if(dataGetShipZone && dataGetShipZone.success=='true' && Object.keys(dataGetShipZone.data).length > 0){
                                Object.keys(dataGetShipZone.data[value].provinces).forEach(function(val){
                                    stateOpt.push({
                                        label:dataGetShipZone.data[value].provinces[val].name,
                                        value: dataGetShipZone.data[value].provinces[val].code
                                    });
                                });
                            }
                            setStateOptions(stateOpt);

                        }}
                        />
                        <Select
                        label="State"
                        id="isa_state_code"
                        name="isa_state_code"
                        placeholder="Select state"
                        options={stateOptions}
                        value={isa_state_code}
                        onChange={(value) => {
                            set_isa_state_code(value);
                            _.map(stateOptions,function(c){
                                if(c.value == value){
                                    set_isa_state(c.label);
                                }
                            });
                        }}
                        />
                        <TextField
                        type="text"
                        label="Zipcode"
                        helpText=""
                        id="isa_zipcode"
                        name="isa_zipcode"
                        value={isa_zipcode}
                        onChange={(value) => {
                            set_isa_zipcode(value);
                        }}
                        />
                    </FormLayout.Group>
                </FormLayout>
            </Card>
          </Layout.Section>
        </Layout>
      </Page>
  );
}
